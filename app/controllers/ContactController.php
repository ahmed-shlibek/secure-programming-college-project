<?php

namespace App\Controllers;

use App\Models\Contact;
use App\Services\R2Storage;

class ContactController
{
    public function submit(): void
    {
        // CSRF check
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/about');
        }

        $ip = clientIp();

        // Rate limiting: cap submissions per IP per window (anti-spam / anti-abuse).
        if (Contact::recentCountForIp($ip, CONTACT_RATE_WINDOW) >= CONTACT_MAX_SUBMISSIONS) {
            flash('error', 'You have sent too many messages recently. Please try again later.');
            redirect('/about#contact');
        }

        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');

        $errors = [];

        if (strlen($name) < 2 || strlen($name) > 100) {
            $errors[] = 'Name must be 2–100 characters.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }
        if (strlen($message) < 10 || strlen($message) > 2000) {
            $errors[] = 'Message must be 10–2000 characters.';
        }

        // Validate the optional PDF attachment (if one was submitted).
        // Returns the validated $_FILES entry, or null if no file was sent.
        $validFile = $this->validateAttachment($errors);

        if (!empty($errors)) {
            flash('error', implode('<br>', array_map('e', $errors)));
            redirect('/about#contact');
        }

        // Upload to R2 *after* all validation passes. If a file was provided but
        // the upload fails, fail closed: do not persist the message.
        $attachKey = $attachName = $attachMime = null;
        $attachSize = null;

        if ($validFile !== null) {
            if (!R2Storage::isConfigured()) {
                flash('error', 'File uploads are temporarily unavailable. Please try again later.');
                redirect('/about#contact');
            }

            // Random, non-guessable object key. The user's filename is never used
            // as the key (prevents path traversal / overwrites); it is kept only
            // for display, sanitized.
            $attachKey  = bin2hex(random_bytes(16)) . '.pdf';
            $attachName = $this->sanitizeFilename($validFile['name']);
            $attachSize = (int) $validFile['size'];
            $attachMime = 'application/pdf';

            try {
                (new R2Storage())->put($validFile['tmp_name'], $attachKey, $attachMime);
            } catch (\Throwable $e) {
                error_log('R2 upload failed: ' . $e->getMessage());
                flash('error', 'Your file could not be uploaded. Please try again.');
                redirect('/about#contact');
            }
        }

        Contact::create($name, $email, $message, $ip, $attachKey, $attachName, $attachSize, $attachMime);

        flash('success', 'Your message has been sent! We\'ll get back to you soon.');
        redirect('/about#contact');
    }

    /**
     * Validate the uploaded PDF, appending any problems to $errors (by reference).
     * Returns the $_FILES['attachment'] entry when a valid file was submitted,
     * or null when no file was submitted. On validation failure returns null and
     * records the reason in $errors.
     */
    private function validateAttachment(array &$errors): ?array
    {
        $file = $_FILES['attachment'] ?? null;

        // No file field, or the user simply left it empty — attachment is optional.
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] !== UPLOAD_ERR_OK) {
            // INI_SIZE / FORM_SIZE / PARTIAL / NO_TMP_DIR / etc.
            $errors[] = 'The file could not be uploaded. Please try again with a smaller PDF.';
            return null;
        }

        // Guard against forged paths: only accept genuine HTTP POST uploads.
        if (!is_uploaded_file($file['tmp_name'])) {
            $errors[] = 'Invalid file upload.';
            return null;
        }

        if ($file['size'] <= 0) {
            $errors[] = 'The uploaded file is empty.';
            return null;
        }
        if ($file['size'] > CONTACT_UPLOAD_MAX_BYTES) {
            $errors[] = 'PDF must be 5 MB or smaller.';
            return null;
        }

        // Content validation: trust the bytes, not the client-supplied type or
        // extension. Detect the real MIME from magic bytes AND confirm the PDF
        // signature header.
        $detectedMime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $header       = (string) file_get_contents($file['tmp_name'], false, null, 0, 5);

        if ($detectedMime !== 'application/pdf' || $header !== '%PDF-') {
            $errors[] = 'Only PDF files are allowed.';
            return null;
        }

        return $file;
    }

    /**
     * Produce a safe, display-only filename: strip any path components, keep a
     * conservative character set, force a .pdf extension, and bound the length.
     */
    private function sanitizeFilename(string $name): string
    {
        $base = basename($name);                                        // drop path parts
        $base = preg_replace('/[^A-Za-z0-9._-]/', '_', $base) ?? '';     // conservative charset
        $base = preg_replace('/\.pdf$/i', '', $base) ?? '';             // normalize extension
        $base = substr($base, 0, 100);
        if ($base === '') {
            $base = 'attachment';
        }
        return $base . '.pdf';
    }
}
