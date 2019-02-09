<?php $this->extend('layout.php', ['title' => 'Edit Profile :: '.$handle]) ?>

<?php $this->start('content') ?>

<?= $content ?>

<h1>Edit Profile: <a href="/user/<?= $this->e($handle) ?>"><?= $this->e($handle) ?></a></h1>

<ul><li><a href="#password">Manage your password</a></li></ul>

<form action="/account-edit.php?handle=<?= $this->e($handle) ?>" method="post" class="pecl-form">

    <h2>Edit Your Information</h2>

    <div>
        <label>Name:</label>
        <input type="text" name="name" size="40" value="<?= $this->e($user['name']) ?>" placeholder="Enter your name" required>
    </div>

    <div>
        <label>Email:</label>
        <input type="text" name="email" size="40" value="<?= $this->e($user['email']) ?>" placeholder="Enter your email" required>
    </div>

    <div>
        <label>Show email address?</label>
        <input type="checkbox" name="showemail" <?= $user['showemail'] ? 'checked' : ''; ?>>
    </div>

    <div>
        <label>Homepage:</label>
        <input type="text" name="homepage" size="40" value="<?= $this->e($user['homepage']) ?>" placeholder="https://example.com">
    </div>

    <div>
        <label>Wishlist URI:</label>
        <input type="text" name="wishlist" size="40" value="<?= $this->e($user['wishlist']) ?>" placeholder="https://example.com">
    </div>

    <div>
        <label>PGP Key ID:<p class="cell_note">(Without leading 0x)</p></label>
        <input type="text" name="pgpkeyid" size="40" value="<?= $this->e($user['pgpkeyid']) ?>" maxlength="20">
    </div>

    <div>
        <label>Additional User Information:<p class="cell_note">(limited to 255 chars)</p></label>
        <textarea name="userinfo" cols="40" rows="5"><?= $this->e($user['userinfo']) ?></textarea>
    </div>

    <div>
        <label>SVN Access:</label>
        <textarea name="cvs_acl" cols="40" rows="5"><?= $this->e($cvsAcl) ?></textarea>
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="handle" value="<?= $this->e($handle) ?>">
    <input type="hidden" name="command" value="update">
    <input type="hidden" name="_fields" value="name:email:showemail:homepage:wishlist:pgpkeyid:userinfo:cvs_acl:submit:handle:command">
</form>


<a name="password"></a>
<h2>&raquo; Manage your password</h2>

<form action="/account-edit.php?handle=<?= $this->e($handle) ?>" method="post" class="pecl-form">

    <h2>Change password</h2>

    <div>
        <label><span class="accesskey">O</span>ld Password:</label>
        <input type="password" name="password_old" size="40" value="" accesskey="o">
    </div>

    <div>
        <label>Password:</label>
        <input type="password" name="password" size="10" value="" required> repeat: <input type="password" name="password2" size="10" value="" required>
    </div>

    <div>
        <label>Remember username and password?</label>
        <input type="checkbox" name="PECL_PERSIST">
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="handle" value="<?= $this->e($handle) ?>">
    <input type="hidden" name="command" value="change_password">
    <input type="hidden" name="_fields" value="password:PECL_PERSIST:submit:handle:command">
</form>


<?php $this->end('content') ?>
