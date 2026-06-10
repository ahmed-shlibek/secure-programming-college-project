<?php

namespace App\Controllers;

use App\Models\Contact;

class ContactController
{
    public function submit(): void
    {
        // CSRF check
        if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            flash('error', 'Invalid security token. Please try again.');
            redirect('/about');
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

        if (!empty($errors)) {
            flash('error', implode('<br>', array_map('e', $errors)));
            redirect('/about');
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        Contact::create($name, $email, $message, $ip);

        flash('success', 'Your message has been sent! We\'ll get back to you soon.');
        redirect('/about#contact');
    }
}
