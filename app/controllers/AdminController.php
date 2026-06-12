<?php

namespace App\Controllers;

use App\Models\Contact;
use App\Services\R2Storage;

class AdminController
{
    /**
     * List contact submissions. Admin only.
     */
    public function contacts(): void
    {
        requireAdmin();

        $contacts   = Contact::all(100);
        $pageTitle  = 'Contact Requests';
        $activePage = 'admin';
        $error      = flash('error');
        $success    = flash('success');
        require __DIR__ . '/../../views/pages/admin/contacts.php';
    }

    /**
     * Fetch one contact's PDF from the private R2 bucket and stream it to the
     * admin as a download. Admin only; auth is re-checked on every click and
     * the bucket is never exposed publicly.
     */
    public function downloadAttachment(string $id): void
    {
        requireAdmin();

        $contact = Contact::findById((int) $id);
        if ($contact === null || empty($contact['attachment_key'])) {
            flash('error', 'Attachment not found.');
            redirect('/admin/contacts');
        }

        if (!R2Storage::isConfigured()) {
            flash('error', 'File storage is not configured.');
            redirect('/admin/contacts');
        }

        try {
            $object = (new R2Storage())->get($contact['attachment_key']);
        } catch (\Throwable $e) {
            error_log('R2 download failed: ' . $e->getMessage());
            flash('error', 'Could not retrieve the file. Please try again.');
            redirect('/admin/contacts');
        }

        // Use the sanitized original name for the download filename, falling back
        // to the object key. Strip anything that could break the header.
        $filename = $contact['attachment_name'] ?: $contact['attachment_key'];
        $filename = preg_replace('/[^A-Za-z0-9._-]/', '_', (string) $filename);

        header('Content-Type: ' . ($contact['attachment_mime'] ?: 'application/pdf'));
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($object['body']));
        header('X-Content-Type-Options: nosniff');
        echo $object['body'];
        exit;
    }
}
