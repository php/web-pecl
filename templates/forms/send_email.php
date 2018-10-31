<?php

foreach ($vars as $key => $var) {
    $vars[$key] = htmlspecialchars($var, ENT_QUOTES);
}

?>

<form action="/account-mail.php?handle=<?= $vars['handle']; ?>" method="post" name="contact" class="pecl-form">

    <div>
        <label>Your name:</label>
        <input type="text" name="name" size="30" value="<?= $vars['name']; ?>" placeholder="Enter your name" required>
    </div>

    <div>
        <label>Your email:</label>
        <input type="email" name="email" size="30" value="<?= $vars['email']; ?>" placeholder="Enter your email" required>
    </div>

    <div>
        <label>Subject:</label>
        <input type="text" name="subject" size="30" value="<?= $vars['subject']; ?>">
    </div>

    <div>
        <label>Text:</label>
        <textarea name="text" cols="35" rows="10" required placeholder="Enter your message"><?= $vars['text']; ?></textarea>
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="_fields" value="name:email:subject:text:submit">
</form>
