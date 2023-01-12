<?php

test('The application is working', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
