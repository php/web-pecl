<?php
// The first field that's empty
$focus = '';

foreach (['name', 'email', 'subject', 'text'] as $key) {
    if (!isset($data[$key]) || '' === $data[$key]) {
        $data[$key] = '';
        ('' === $focus) ? $focus = $key : '';
    }
}

$vars = [
    'handle' => $_GET['handle'],
    'name' => $data['name'],
    'email' => $data['email'],
    'subject' => $data['subject'],
    'text' => $data['text'],
];
?>

<table cellpadding="0" cellspacing="1" style="width: 90%; border: 0px;">
    <tr>
        <td bgcolor="#000000">
            <table cellpadding="2" cellspacing="1" style="width: 100%; border: 0px;">
                <tr style="background-color: #CCCCCC;">
                    <th>Send email</th>
                </tr>
                <tr bgcolor="#ffffff">
                    <td>

<form action="/account-mail.php?handle=<?= $this->e($vars['handle']); ?>" method="post" name="contact" class="pecl-form">
    <div>
        <label>Your name:</label>
        <input type="text" name="name" size="30" value="<?= $this->e($vars['name']); ?>" placeholder="Enter your name" required>
    </div>

    <div>
        <label>Your email:</label>
        <input type="email" name="email" size="30" value="<?= $this->e($vars['email']); ?>" placeholder="Enter your email" required>
    </div>

    <div>
        <label>Subject:</label>
        <input type="text" name="subject" size="30" value="<?= $this->e($vars['subject']); ?>">
    </div>

    <div>
        <label>Text:</label>
        <textarea name="text" cols="35" rows="10" required placeholder="Enter your message"><?= $this->e($vars['text']); ?></textarea>
    </div>

    <div>
        <label>&nbsp;</label>
        <input type="submit" name="submit" value="Submit">
    </div>

    <input type="hidden" name="_fields" value="name:email:subject:text:submit">
</form>

                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<script>
    document.forms.contact.<?= $focus ?>.focus();
</script>
