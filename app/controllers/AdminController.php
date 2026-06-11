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
     * Mint a fresh, short-lived presigned R2 URL for one contact's PDF and
     * redirect to it. Admin only; auth is re-checked on every click and the
     * bucket stays private.
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
            $url = (new R2Storage())->presignedGetUrl($contact['attachment_key'], 5);
        } catch (\Throwable $e) {
            error_log('R2 presign failed: ' . $e->getMessage());
            flash('error', 'Could not generate a download link. Please try again.');
            redirect('/admin/contacts');
        }

        // External URL — bypass the BASE_URL-prefixing redirect() helper.
        header('Location: ' . $url);
        exit;
    }
}
