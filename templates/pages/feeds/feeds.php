<?xml version="1.0" encoding="utf-8"?>
<rdf:RDF
        xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
        xmlns="http://purl.org/rss/1.0/"
        xmlns:dc="http://purl.org/dc/elements/1.1/">

    <channel rdf:about="<?= $this->e($url) ?>">
        <link><?= $this->e($url) ?></link>
        <dc:creator>php-webmaster@lists.php.net</dc:creator>
        <dc:publisher>php-webmaster@lists.php.net</dc:publisher>
        <dc:language>en-us</dc:language>
        <items>
            <rdf:Seq>

                <?php foreach ($items as $item): ?>
                    <rdf:li rdf:resource="<?= $this->e($item['link']) ?>"/>
                <?php endforeach ?>

            </rdf:Seq>
        </items>

        <title><?= $this->e($channelTitle) ?></title>
        <description><?= $this->e($channelDescription) ?></description>
    </channel>

    <?php foreach ($items as $item): ?>
        <item rdf:about="<?= $item['link'] ?>">
            <title><?= (!empty($item['version'])) ? $this->e($item['name'].' '.$item['version']) : $this->e($item['name']) ?></title>
            <link><?= $this->e($item['link']) ?></link>
            <description>
                <?= $this->e($item['releasenotes']) ?>
            </description>
            <dc:date><?= date('Y-m-d\TH:i:s-05:00', strtotime($item['releasedate'])) ?></dc:date>
        </item>
    <?php endforeach ?>

</rdf:RDF>
