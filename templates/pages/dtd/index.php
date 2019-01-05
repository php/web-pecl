<?php $this->extend('layout.php', ['title' => 'Document Type Definitions']) ?>

<?php $this->start('content') ?>

<h2>Document Type Definitions</h2>

<p>The following Document Type Definitions are used in PECL:</p>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th colspan="2">Available DTDs</th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td valign="top"><a href="/dtd/package-1.0">package-1.0</a></td>

                    <td>This is the <acronym title="Document Type Definition">DTD</acronym>
                        that defines the legal building blocks of the <tt>package.xml</tt>
                        file that comes with each package. More information about
                        <tt>package.xml</tt> can be found
                        <a href="https://pear.php.net/manual/en/developers.packagedef.php">in the manual</a>.
                        <br /><br />A <a href="/dtd/package-1.0.xsd">
                        <acronym title="XML Schema Definition">XSD</acronym> file</a> is
                        available as well. (Slightly outdated)
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<?php $this->end('content') ?>
