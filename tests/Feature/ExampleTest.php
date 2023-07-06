<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
   public function testFetchData()
{
    // Make a request to the fetchData endpoint
    $response = $this->get('/fetchdata');

    // Assert that the response is successful
    $response->assertOk();

    // Assert the data and counts returned in the response
    $responseData = $response->json();
    $data = $responseData[0];
    $counts = $responseData[1];

    $this->assertNotEmpty($data);
    $this->assertNotEmpty($counts);
    // Perform any additional assertions on the data and counts as needed
}

}
