<?php $this->extend('layout.php', ['title' => 'Search']) ?>

<?php $this->start('content') ?>

<h2>Search</h2>

<p style="color:#990000">
    <b>
        <form action="/search.php" method="post" class="pecl-form">
            <div>
                <label>Search for</label>
                <input type="text" name="search_string" value="<?= $this->noHtml($query) ?>" placeholder="Search for...">
            </div>

            <div>
                <label>in the</label>
                <select name="search_in">
                    <option value="packages">Packages</option>
                    <option value="site">This site (using Google)</option>
                    <option value="developers">Developers</option>
                    <option value="pecl-dev">Developers mailing list</option>
                    <option value="pecl-cvs">Code commits mailing list</option>
                </select>
            </div>
            <div>
                <label>&nbsp;</label>
                <input type="submit" value="Search">
            </div>
        </form>
    </b>
</p>

<?php $this->end('content') ?>
