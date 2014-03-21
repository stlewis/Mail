## About Mail

This class is a no-frills way to send emails. I wrote it originally as part of my DirtyMVC framework, because I wanted to reduce/eliminate that application's dependency on external libraries, (such as PEAR Mail). Because it occurs to me that there might be other people out there that could use a reasonably powerful email library, who need it to be portable and not dependent upon something as heavy as PEAR, I decided to extract my Mail class from the DirtyMVC framework, and I'm making it available here.

Just a quick overview of some of the features before I move on to examples:

* Sends mixed/multipart emails, (html and/or plaintext).
* Sends attachments
* Object-oriented.
* Uses PHP's built-in mail() function.

## Examples

### Sending A Basic Text Email:
```php
<?php
    $to      = "joe@example.com";
    $from    = "jim@example.com";
    $subject = "Testing Text Email";
    $body    = "This is a test email body that will be in text format";
    $mail    = new Mail($to, $from, $subject, $body);
    $mail->send();
?>
```

It's that simple.  Granted, it's not a heck of a lot harder to do the same thing just using PHP's built-in mail() function,
so let's take a look at a slightly more complicated example...

### Sending An Email With Text and HTML:

```php
<?php
    $to        = "joe@example.com";
    $from      = "jim@example.com";
    $subject   = "Testing Text Email";
    $text_body = "This is a test email body that will be in text format";
    $html_body = "<p>This is a <strong>test email body</strong> in HTML</p>";
    $mail      = new Mail($to, $from, $subject, $text_body, $html_body);
    $mail->send();
?>
```

Given the code above, email clients that can support HTML in their messages will get the HTML version of the email, whereas email clients
that only support text will render the text version instead. Good luck making that happen with just one extra line of code using mail()!

### Adding Additional Headers

```php
<?php
    $to        = "joe@example.com";
    $from      = "jim@example.com";
    $subject   = "Testing Text Email";
    $text_body = "This is a test email body that will be in text format";
    $html_body = "<p>This is a <strong>test email body</strong> in HTML</p>";

    $mail      = new Mail($to, $from, $subject, $text_body, $html_body);

    $mail->add_header("Bcc: someone@example.com");
    $mail->add_header("Cc: someone_else@example.com");
    $mail->add_header("Reply-To: email_me@example.com");

    $mail->send();

?>
```

Although I debated the usefulness of adding members like 'cc' and 'bcc' to the Mail class, I felt in the end that it made far more sense to just provide an easy way to add any header at all to the email.  In the example above, I show you how to add some common headers to an email, but you could add any header you need to using the same method.  The only thing to bear in mind is that the string you pass _must_ be a valid header string.


h3. Adding Attachments

```php
<?php
    $to        = "joe@example.com";
    $from      = "jim@example.com";
    $subject   = "Testing Text Email";
    $text_body = "This is a test email body that will be in text format";
    $html_body = "<p>This is a <strong>test email body</strong> that will be in html format</p>";
    $mail      = new Mail($to, $from, $subject, $text_body, $html_body);

    $mail->add_attachment("/path/to/my_attachment.file");
    $mail->add_attachment("/path/to/my_other_attachment.file");

    $mail->send();
?>
```

To add an attachment to an email, all you have to do is call the add_attachment() method with the absolute path to the file you'd like to attach as the only argument. It's that simple.
