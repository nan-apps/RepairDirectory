<?php

namespace TheRestartProject\RepairDirectory\Tests\Feature\Http\Controllers\Admin;

use TheRestartProject\Fixometer\Domain\Entities\User;
use TheRestartProject\RepairDirectory\Domain\Enums\PublishingStatus;
use TheRestartProject\RepairDirectory\Domain\Models\Point;
use TheRestartProject\RepairDirectory\Domain\Repositories\BusinessRepository;
use TheRestartProject\RepairDirectory\Testing\DatabaseMigrations;
use TheRestartProject\RepairDirectory\Testing\FixometerDatabaseMigrations;
use TheRestartProject\RepairDirectory\Tests\IntegrationTestCase;

/**
 * Admin\BusinessController Test
 *
 * @category Test
 * @package  TheRestartProject\RepairDirectory\Tests\Feature\Http\Controllers\Admin;
 * @author   Joaquim d'Souza <joaquim@outlandish.com>
 * @license  GPLv2 https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html
 * @link     http://tactician.thephpleague.com/
 */
class BusinessControllerTest extends IntegrationTestCase
{
    use DatabaseMigrations;
    use FixometerDatabaseMigrations;
    /**
     * Asserts that the BusinessController->create function creates a new Business
     * and persists this in the database
     *
     * @return void
     *
     * @test
     */
    public function test_create()
    {
        $user = entity(User::class)->create(['role' => User::HOST]);
        $this->be($user);

        $response = $this->post(
            route('admin.business.create'), [
                'name' => 'iRepair Centre Bath',
                'description' => 'Bath\'s iRepair Centre. Fix all your broken devices.',
                'address' => '12 Westgate St, Bath',
                'postcode' => 'BA1 1EQ',
                'city' => 'Bath',
                'localArea' => 'Somerset',
                'mobile' => '07761901775',
                'landline' => '07141200908',
                'website' => 'outlandish.com'
            ]
        );
        $response->assertStatus(302);

        $this->assertDatabaseHas(
            'businesses', [
                'uid' => 7,
                'name' => 'iRepair Centre Bath',
                'description' => 'Bath\'s iRepair Centre. Fix all your broken devices.',
                'address' => '12 Westgate St, Bath',
                'postcode' => 'BA1 1EQ',
                'city' => 'Bath',
                'local_area' => 'Somerset',
                'mobile' => '07761901775',
                'landline' => '07141200908',
                'website' => 'outlandish.com'
            ]
        );

        $businessRepository = $this->app->make(BusinessRepository::class);

        $this->assertEquals(new Point(51.3813963, -2.3613877), $businessRepository->findById(7)->getGeolocation());
    }

    /**
     * Asserts that the BusinessController->update function updates an existing Business
     * and persists this in the database
     *
     * @return void
     *
     * @test
     */
    public function test_update()
    {
        $user = entity(User::class)->create(['role' => User::HOST]);
        $this->be($user);

        $response = $this->put(
            route('admin.business.update', ['id' => 1]), [
                'name' => 'This is a new name',
                'description' => 'This is a new description.',
                'address' => '12 Westgate St, Bath',
                'postcode' => 'BA1 1EQ'
            ]
        );
        $response->assertStatus(302);

        $this->assertDatabaseHas(
            'businesses', [
                'uid' => 1,
                'name' => 'This is a new name',
                'description' => 'This is a new description.'
            ]
        );
    }
}
