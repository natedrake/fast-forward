<?php

namespace phparsenal\fastforward;

use cli\Streams;
use nochso\ORM\DBA\DBA;

class Client
{
    /**
     * @var string
     */
    private $folder;

    /**
     * @var string
     */
    private $batchPath;

    /**
     * @var array
     */
    private $args;

    function __construct()
    {
        $this->init();
    }

    private function init()
    {
        $this->folder = dirname($_SERVER['PHP_SELF']);
        chdir($this->folder);

        // Prevent the previous command from being executed in case anything fails later on
        $this->batchPath = $this->folder . DIRECTORY_SEPARATOR . 'cli-launch.temp.bat';
        file_put_contents($this->batchPath, '');

        DBA::connect('sqlite:./db.sqlite', '', '');
        $this->ensureSchema();
    }

    /**
     * @param array $argv
     */
    public function run($argv)
    {
        $this->args = $argv;

        if (count($this->args) > 1) {
            // ff add <args>
            if ($this->args[1] == "add") {
                $this->addBookmark(array_slice($this->args, 2));
            } else {
                // ff <search>
                $this->runBookmark(array_slice($this->args, 1));
            }
        } else {
            // Show a list and let the user decide
            // ff
            $this->runBookmark(array());
        }

    }

    public function addBookmark($args)
    {
        $bookmark = new Model\Bookmark();
        $count = count($args);
        // Get as much as you can
        switch ($count) {
            case 3:
                $bookmark->command = $args[2];
            case 2:
                $bookmark->description = $args[1];
            case 1:
                $bookmark->shortcut = $args[0];
        }

        // Ask for what's left
        switch ($count) {
            case 0:
                $bookmark->shortcut = Streams::prompt("Shortcut for easy searching");
            case 1:
                $bookmark->description = Streams::prompt("The description of the command");
            case 2:
                $bookmark->command = Streams::prompt("Command to be executed");
        }
        $bookmark->save();
        Streams::out("New bookmark was saved: " . $bookmark->shortcut);
    }

    public function runBookmark($args)
    {
        $query = Model\Bookmark::select();
        foreach ($args as $arg) {
            $query->like('shortcut', $arg . '%');
        }
        $query->orderDesc('hit_count');
        $bookmarks = $query->all();
        $bm = $this->selectBookmark($bookmarks, $args);
        if ($bm !== null) {
            $bm->run($this);
        }
    }


    /**
     * @param $bookmarks
     * @param array $args
     * @return Model\Bookmark|null
     * @throws Exception
     */
    public function selectBookmark($bookmarks, $args)
    {
        if (count($bookmarks) == 1) {
            /** @var Model\Bookmark $bm */
            $bm = $bookmarks->current();
            if ($bm->shortcut == $args[0]) {
                return $bm;
            }
        }

        $map = array();
        $i = 0;
        $table = new \cli\Table();
        $table->setHeaders(['#', 'Shortcut', 'Description', 'Command', 'Hits']);
        $rows = array();
        foreach ($bookmarks as $id => $bm) {
            $map[$i] = $id;
            $rows[] = array($i, $bm->shortcut, $bm->description, $bm->command, $bm->hit_count);
            $i++;
        }
        $table->setRows($rows);
        $r = new \cli\table\Ascii();
        $r->setCharacters(array(
            'corner' => '',
            'line' => '',
            'border' => ' ',
            'padding' => '',
        ));
        $table->setRenderer($r);
        $table->display();
        Streams::out("Which # do you want to run? ");
        $num = Streams::input();
        if (isset($map[$num])) {
            return $bookmarks[$map[$num]];
        }
        return null;
    }

    /**
     * Prepares the database when it is new.
     */
    public function ensureSchema()
    {
        $sql = "SELECT COUNT(*) FROM sqlite_master";
        $count = (int)DBA::execute($sql)->fetchColumn();
        if ($count !== 0) {
            return;
        }
        echo "Database is new. Trying to set up database schema..\n";
        $schemaPath = "asset/model.sql";
        $exit = false;
        if (!is_file($schemaPath)) {
            echo "Schema file could not be found: $schemaPath\n";
            echo "Please make sure that you have this file.\n";
            echo "\nExiting.\n";
            exit;
        }
        $schemaSql = file_get_contents($schemaPath);
        if ($schemaSql === false) {
            echo "Unable to read schema file: " . $schemaPath . "\n";
            echo "\nExiting.\n";
            exit;
        }
        $schemaSqlList = explode(';', $schemaSql);
        $count = count($schemaSqlList);
        foreach ($schemaSqlList as $key => $singleSql) {
            echo "\r" . ($key + 1) . '/' . $count . ' ';
            $statement = DBA::prepare($singleSql);
            if ($statement->execute() === true) {
                echo "Ok.";
            } else {
                echo "Failed:\n";
                var_dump($statement->errorInfo());
                echo "\nWhile trying to run:\n";
                echo $singleSql . "\n";
                echo "Exiting.\n";
                exit;
            }
        }
        echo "\nDatabase is ready.\n";
    }

    /**
     * Converts an integer to its ordinal number
     *
     * <code>
     * ordinal(1) === "1st"
     * ordinal(32) === "32nd"
     * </code>
     *
     * @param int $number
     * @return string
     */
    public function ordinal($number)
    {
        $ends = array('th', 'st', 'nd', 'rd', 'th', 'th', 'th', 'th', 'th', 'th');
        if ((($number % 100) >= 11) && (($number % 100) <= 13)) {
            return $number . 'th';
        } else {
            return $number . $ends[$number % 10];
        }
    }

    /**
     * @return string
     */
    public function getBatchPath()
    {
        return $this->batchPath;
    }
}