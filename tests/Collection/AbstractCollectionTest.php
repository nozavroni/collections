<?php
/**
 * Nozavroni/Collections
 * Just another collections library for PHP5.6+.
 *
 * @version   {version}
 * @copyright Copyright (c) 2016 Luke Visinoni <luke.visinoni@gmail.com>
 * @author    Luke Visinoni <luke.visinoni@gmail.com>
 * @license   https://github.com/deni-zen/csvelte/blob/master/LICENSE The MIT License (MIT)
 */
namespace NozTest\Collection;

use Noz\Collection\Collection;
use NozTest\UnitTestCase;
use Faker;

class AbstractCollectionTest extends UnitTestCase
{
    /**
     * Do not change this it will break the tests
     */
    const MY_BDAY = 19860423;

    const MultiCollection = 'multi';
    const TabularCollection = 'table';

    protected $testdata = [];

    public function setUp()
    {
        parent::setUp();
        $faker = Faker\Factory::create();
        $faker->seed(static::MY_BDAY);
        $this->testdata['multi'] = [
            'names' => [],
            'addresses' => [],
            'cities' => [],
            'dates' => [],
            'numeric' => [],
            'words' => [],
            'userAgent' => []
        ];
        for ($i = 0; $i < 10; $i++) {
            $this->testdata[static::MultiCollection]['names'][] = $faker->name;
            $this->testdata[static::MultiCollection]['addresses'][] = $faker->streetAddress;
            $this->testdata[static::MultiCollection]['cities'][] = $faker->city;
            $this->testdata[static::MultiCollection]['dates'][] = $faker->date;
            $this->testdata[static::MultiCollection]['numeric'][] = $faker->randomNumber;
            $this->testdata[static::MultiCollection]['words'][] = $faker->words;
            $this->testdata[static::MultiCollection]['userAgent'][] = $faker->userAgent;
        }
        $this->testdata[static::TabularCollection] = [
            'user' => [],
            'profile' => []
        ];
        for($t = 1; $t <= 5; $t++) {
            $created = $faker->dateTimeThisYear->format('YmdHis');
            $profile_id = $t + 125;
            $this->testdata[static::TabularCollection]['user'][] = [
                'id' => $t,
                'profile_id' => $profile_id,
                'email' => $faker->email,
                'password' => sha1($faker->asciify('**********')),
                'role' => $faker->randomElement(['user','admin','user','user','user','user','user','moderator','moderator']),
                'is_active' => $faker->boolean,
                'created' => $created,
                'modified' => $created
            ];
            $this->testdata[static::TabularCollection]['profile'][] = [
                'id' => $profile_id,
                'address' => $faker->streetAddress,
                'city' => $faker->city,
                'state' => $faker->stateAbbr,
                'zipcode' => $faker->postcode,
                'phone' => $faker->phoneNumber,
                'bio' => $faker->paragraph,
                'created' => $created,
                'modified' => $created
            ];
        }
        $this->testdata[Collection::class] = $faker->words(15);
    }
}