<?php

test('the root redirects guests into the authenticated chat flow', function (): void {
    $this->get('/')->assertRedirect('/chat');
});

test('legacy public maintenance endpoints are not exposed', function (): void {
    $this->get('/system/catch-clear')->assertNotFound();
    $this->get('/system/storage-link')->assertNotFound();
});
