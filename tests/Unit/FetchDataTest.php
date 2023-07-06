<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class FetchDataTest extends TestCase
{
    /**
     * testFetchData
     *
     * @return void
     */
    public function testFetchData()
{
    // Make a request to the fetchData endpoint
    $response = $this->get('/fetch-data');

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
