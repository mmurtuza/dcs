<?php

namespace Tests\Unit;

use Tests\TestCase;

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
    $response = $this->post('/fetchdata');
    // $response = $this->getStatus()

    // Assert that the response is successful
    $response->assertOk();

    // Assert the data and counts returned in the response
    $responseData = $response->json();
    $data = $responseData['data'];
    $counts = $responseData['counts'];
        print_r( [$data, $counts]);

    $this->assertNotEmpty($data);
    $this->assertNotEmpty($counts);
    // Perform any additional assertions on the data and counts as needed
}

}
