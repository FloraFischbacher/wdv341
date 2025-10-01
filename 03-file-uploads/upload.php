<?php
// Calling me paranoid over this assignment's implications is a bit of an
// understatement, truthfully.
//
// I need to absolutely make sure I'm doing everything I can to avoid at least
// the *obvious* exploits when working on this: XSS (or worse, remote-code
// execution via uploaded executables), path traversals, file bombs, bypassing
// the interface through HTTP requests formed from other kinds of clients like
// cURL, etc. I also think it would be wise to have logging for file uploads
// just to make sure that there aren't any big issues.
//
// In general, nothing *should* happen with the input form for this assignment.
// But, I don't wish to learn the hard way *again* about the value of proper
// security practices, because I've already been burned about this in the past
// (however minimal the damage may have been in those instances). This means
// going the extra mile, not necessarily because I want to but because I kind
// of have to. I really don't want to take any chances that I breach anything,
// even while I acknowledge that I am simply hosting a personal website and not
// company/government secrets or user data.
//
// Passionate speech about a problem I could (but won't necessarily) face out
// of the way, I want to draft up a plan to make sure that allowing files to be
// uploaded won't completely pwn me the instant I put this page to the server.
//
// A sibling of mine (who is currently working towards a bachelor's degree in
// cybersecurity!) directed me towards the Open Worldwide Application Security
// Project (OWASP) Foundation's Top Ten [1] rankings (which are a list of the
// ten most critical web vulnerabilities, often by virtue of their commonality
// and ease-of-exploitation), as well as their Cheat Sheet Series [2], of which
// I will be basing my practices on what they recommend for hardening file
// uploads specifically to the best of my abilities (given that some advice is
// either far too time-consuming or inapplicable/unactionable given my
// circumstances (i.e. hosting file uploads on a separate server entirely)). It
// is a seriously invaluable resource, and I'll definitely be returning to it
// as I continue to work in this class.
//
// [1]: https://owasp.org/www-project-top-ten/
// [2]: https://cheatsheetseries.owasp.org/cheatsheets/File_Upload_Cheat_Sheet.html

// Let's start with the filetype validation. I want to allow static image
// files, so I will start by only allowing the following MIME types. We can use
// PHP's `mime_content_type()` function [3] to acquire an uploaded file's type
// on the server's end, using our own `magic.mime` file to make sure that
// the file's contents do in fact match the expected "magic numbers" [4] that
// are associated with that file type.
//
// WARNING: We absolutely, positively, *cannot* rely on the `Content-Type`
// header of the HTTP request that submits the file. That header can easily be
// spoofed, and that would be extremely bad! In PHP, this is acquired with the
// $_FILES superglobal under the `type` key. Funnily enough, PHP's
// documentation itself issues this same warning:
//
// > This mime type is however not checked on the PHP side and therefore don't
// > take its value for granted. [5]
//
// [3]: https://www.php.net/manual/en/function.mime-content-type.php
// [4]: https://en.wikipedia.org/wiki/List_of_file_signatures
// [5]: https://www.php.net/manual/en/features.file-upload.post-method.php

enum ImageType {
    case JPEG;
    case PNG;
    case WEBP;
}

$allowedFileTypes = [
    // I'm storing values for the extensions just as a nice thing to have for
    // when I go to store them on the disk.
    "image/jpeg" => ImageType::JPEG,
    "image/png"  => ImageType::PNG,
    "image/webp" => ImageType::WEBP,
];

// An 8 megabyte upload size is generous for images, especially ones that are
// lossily compressed like .jpg files.
$maxFileSize = 8 * 1024 * 1024;
$maxNameLength = 100;

const TEMP_UPLOADS_DIR = "/opt/www/school/wip/";
const PERM_UPLOADS_DIR = "/opt/www/school/up/";

function getImgExt(ImageType $type): string {
    return match ($type) {
        ImageType::JPEG => "jpg",
        ImageType::PNG  => "png",
        ImageType::WEBP => "webp",
    };
}

function validateImage() {
    global $allowedFileTypes;
    global $maxFileSize;
    global $maxNameLength;

    $uploadAttempt = $_FILES["uploaded"];

    // Very basic stuff. Whether a file was submitted at all, essentially.
    if (empty($uploadAttempt))
        throw new RuntimeException("No files were submitted.");

    if (!isset($_POST["submit"]))
        throw new RuntimeException("Files were not submitted through the form.");

    if ($uploadAttempt["error"] != 0)
        throw new RuntimeException("An error occurred while uploading the file.");

    // Multiple files at a time are not allowed.
    if (is_array($uploadAttempt["tmp_name"]))
        throw new RuntimeException("Only one file is allowed to be submitted.");

    // I made sure that the `magic.mime` file on the server is accurate, as
    // this required additional setup on my end! Mostly commenting this in
    // the event that this doesn't *quite* work on other setups.
    $clientMimeType = $uploadAttempt["type"];
    $serverMimeType = mime_content_type($uploadAttempt["tmp_name"]);

    if (
        $serverMimeType == false
        || !array_key_exists($serverMimeType, $allowedFileTypes)
    ) {
        return false;
    } elseif ($clientMimeType != $serverMimeType) {
        // This means something either went *very* wrong on the user's end or
        // (maybe more likely) malicious activity happened here.
        //
        // I want to make sure I'm alerted when this happens, so let's do that.

        $now = new DateTime(null, new DateTimeZone("America/Chicago"));
        $logTime = $now->format("Y-m-d h:i:s A");

        error_log("[" . $logTime . "] Attempted client-side MIME type spoof?");

        throw new RuntimeException("An error occurred while uploading the file.");
    }

    if ($uploadAttempt["size"] > $maxFileSize)
        throw new RuntimeException("File exceeds the file size limit (8 megabytes).");

    $type = $allowedFileTypes[$serverMimeType];
    $ext = getImgExt($type);

    $new = sanitizeName($uploadAttempt["name"], $ext);
    $old = $uploadAttempt["tmp_name"];

    if (mb_strlen($new, "UTF-8") > $maxNameLength)
        throw new RuntimeException("File name exceeds the length limit (100 chars).");

    move_uploaded_file($old, $new);

    return [
        "new_dir" => $new,
        "old_dir" => $old,
        "type" => $type,
    ];
}

function sanitizeName(string $originalName, string $ext): string {
    $trimmed = mb_scrub(trim($originalName));
    // $trimmed = mb_scrub(mb_trim($originalName)); // PHP 8.4+ (mb_trim)
    $lower = mb_convert_case($trimmed, MB_CASE_LOWER);
    $ascii = mb_convert_encoding($lower, "ASCII", "UTF-8");
    $base = mb_ereg_replace("\..*", "", basename($ascii));
    $spaces = mb_ereg_replace("\s", "_", $base);
    $filtered = mb_ereg_replace("[^a-z0-9\.\-\_]", "", $spaces);

    return TEMP_UPLOADS_DIR . $filtered . "." . $ext;
}

// Of course, we can't just take the image from there--we have to absolutely
// make sure that we strip the file of any possible metadata it has! For users
// this removes a source of privacy violations, and for me it removes the
// possibility of security exploits via EXIF data. While this might be overly
// paranoid of a measure to take, the name of the game is reducing the amount
// of possible attack vectors.
//
// I will not be doing this myself, because this is a solved problem that many
// trusted libraries exist to handle, including GD, ImageMagick, and (what I'm
// using, called) `libvips` [7], a very lightweight image processing library
// that is used by Mastodon, Wikipedia, and Ruby on Rails (to name a few). It
// has a corresponding PHP binding in the form of `php-vips` [8], which I took
// the liberties of learning to use on my end. :)
//
// [7]: https://www.libvips.org/
// [8]: https://github.com/libvips/php-vips

require __DIR__ . '/vendor/autoload.php';
use Jcupitt\Vips;

// (NOTE: Yes, I had to learn how to use `composer` for this. The install
// instructions did not mention any other way, and I am in no position to hack
// together an alternative. From what I can tell, though, it appears to be a
// very par-for-the-course kind of program, which makes me avoid wincing over
// it *too* hard.)

function sanitizeImage(array $validated) {
    // I'm basing this off of an example [9] that loads the image contents into
    // a buffer and attempts to keep parts of the metadata, except I'm
    // explicitly attempting to keep *no* metadata in the returned image.
    //
    // [9]: https://github.com/libvips/php-vips/blob/master/examples/keep.php
    $original = Vips\Image::newFromFile($validated["new_dir"]);

    // Because libvips' save-to-buffer function doesn't *quite* support WebP
    // without calling its own method, I just store an anonymous function in
    // `$writeBuffer` with a reference to the proper method and then call
    // the method stored based on the exhaustive pattern match.
    $writeToBuffer = match($validated["type"]) {
        ImageType::JPEG => fn() => $original->jpegsave_buffer(
            ["keep" => Vips\ForeignKeep::NONE]),
        ImageType::PNG  => fn() => $original->pngsave_buffer(
            ["keep" => Vips\ForeignKeep::NONE]),
        ImageType::WEBP => fn() => $original->webpsave_buffer(
            ["keep" => Vips\ForeignKeep::NONE]),
    };

    $imgBuffer = $writeToBuffer();
    $result = Vips\Image::newFromBuffer($imgBuffer, "");

    // And we have to do the same to write to a file, but this time it is
    // because each file type has its own particular settings that we can
    // tweak to change how the file is saved.
    $writeToFile = match($validated["type"]) {
        // For JPEGs, we don't want to do *too* much processing to them.
        // They are usually already very compressed.
        ImageType::JPEG => fn($f) => $result->jpegsave($f, ["Q" => 95]),
        ImageType::PNG  => fn($f) => $result->pngsave($f, [
            "compression" => 5,
            "effort"      => 5,   // on a scale of 1-10...
        ]), // Prioritizing speed over compression amount.
        ImageType::WEBP => fn($f) => $result->webpsave($f, [
            "near_lossless" => true,
            "Q"             => 80,
            "effort"        => 3, // ...but this one is 1-6, I guess?
        ]), // Decided on a lossy-ish style of compression for WebP files.
    };

    $writeToFile($validated["new_dir"]);

    return [
        "name" => $validated["new_dir"],
        "old_dir" => $validated["old_dir"],
        "type" => $validated["type"],
    ];
}

function processUploads() {
    // Validate and then sanitize the uploaded image (if it is valid).
    try {
        $validated = validateImage();
        $sanitized = sanitizeImage($validated);

        $finalPath = PERM_UPLOADS_DIR . hash_file("sha256", $sanitized["name"])
        . "." . getImgExt($sanitized["type"]);
        rename($sanitized["name"], $finalPath);

        $image = basename($sanitized["name"]);

        // Finally, we need to symlink our uploaded images to the web-root.
        $imgLink = __DIR__ . "/images/" . $image;
        symlink($finalPath, $imgLink);

        $result = <<<END
        <h1>File upload succeeded</h1>
        <p>You may find your uploaded image at the following link:</p>
        <ul>
            <li><a href="./images/$image">https://flora-f.dev/wdv341/03-file-uploads/images/$image</a></li>
        </ul>
        <p>Please keep in mind that this site is a demonstration of file uploading capabilities, and is not a file hosting service. All uploaded images will be removed nightly, by midnight CST. Thank you!</p>
        <a href="./">Return to upload form</a>
        END;

        return $result;
    } catch (RuntimeException $err) {
        unlink($_FILES["uploaded"]["tmp_name"]);

        $result = <<<END
        <h1>File upload failed</h1>
        <p>The following issue was found with your request:</p>
        <ul>
        <li>{$err->getMessage()}</li>
        </ul>
        <a href="./">Return to upload form</a>
        END;

        return $result;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="ie=edge">
<title>5-1: File Uploads</title>
</head>
<body>
    <?php
        // I'd try to make it look a little nicer but I'm very tired.
        // Oh well... =.='
    ?>
    <?= processUploads(); ?>
</body>
</html>
