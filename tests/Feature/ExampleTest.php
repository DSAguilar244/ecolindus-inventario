<?php

it('returns a successful response', function () {
    /** @var \Tests\TestCase $this */
    $response = $this->get('/');

    if ($response->status() === 302) {
        $location = $response->headers->get('Location');
        $response = $this->get($location);
    }

    $response->assertStatus(200);
});
