<?php
/**
 * Craft Greece counties plugin for Craft CMS 3.x
 *
 * Migrate the counties of Greece for craft commerce
 *
 * @link      https://codechrisz.github.io/
 * @copyright Copyright (c) 2022 codechrisz
 */

namespace codechrisz\craftgreececounties\migrations;

use Craft;
use craft\db\Migration;
use craft\commerce\Plugin as craftCommerce;
use craft\commerce\records\Country;
use craft\commerce\records\State;
/**
 * Install migration.
 */
class Install extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {

        // IF CC check
        if (class_exists('craft\commerce\Plugin')) {
            $countyList = file_get_contents( __DIR__ . '/../counties.json');
            $counties = (array)json_decode($countyList);

            // Current list only for Greece
            // First level of the json is the country iso
            $countries = Country::find()->where(['in', 'iso', array_keys($counties)])->all();
            $countryIdList = [];
            foreach ($countries as $record) {
                $countryIdList[$record->iso] = $record->id;
            }

            $rows = [];
            foreach ($counties as $iso => $list) {
                $sortNumber = 1;
                foreach ($list as $abbr => $name) {
                    $rows[] = [$countryIdList[$iso], $abbr, $name, $sortNumber];
                    $sortNumber++;
                }
            }

            $this->batchInsert(State::tableName(), ['countryId', 'abbreviation', 'name', 'sortOrder'], $rows);
            // Craft::dd($countyData);
        }
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Place uninstallation code here...

        // IF CC check
        if (class_exists('craft\commerce\Plugin')) {
            $countyData = file_get_contents( __DIR__ . '/../counties.json');
            $states = (array)json_decode($countyData);

            /** @var ActiveRecord $countries */
            $countries = Country::find()->where(['in', 'iso', array_keys($states)])->all();
            foreach ($countries as $record) {
                $this->delete(State::tableName(),'countryId=' . $record->id);
            }
        }
    }
}