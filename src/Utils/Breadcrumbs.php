<?php

namespace App\Utils;

use App\Database;

/**
 * Breadcrumbs generator for packages and categories.
 */
class Breadcrumbs
{
    /**
     * Database handler.
     *
     * @var Database
     */
    private $database;

    /**
     * Class constructor.
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Get breadcrumbs for categories and packages.
     * Top Level :: Multimedia :: Audio :: FliteTTS
     *
     * @param int $id
     * @param bool $isLastLink If the last category should or not be a link
     * @return string
     */
    public function getBreadcrumbs($id, $isLastLink = false)
    {
        $html = '<a href="/packages.php">Top Level</a>';

        if (null !== $id) {
            $sql = "SELECT c.id, c.name
                    FROM categories c, categories cat
                    WHERE cat.id = :id
                        AND c.cat_left <= cat.cat_left
                        AND c.cat_right >= cat.cat_right
            ";

            $results = $this->database->run($sql, [':id' => $id])->fetchAll();
            $nrows = count($results);

            $i = 0;
            foreach ($results as $row) {
                if (!$isLastLink && $i >= $nrows -1) {
                    break;
                }

                $html .= ' :: <a href="/packages.php?catpid='.$row['id'].'&catname='.$row['name'].'">'.$row['name'].'</a>';
                $i++;
            }

            if (!$isLastLink) {
                $html .= ' :: <b>'.$row['name'].'</b>';
            }
        }

        return $html;
    }
}
