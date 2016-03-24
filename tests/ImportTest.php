<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class ImportTest extends TestCase
{
    /**
     * Importer test.
     *
     * @return void
     */
    public function testImport()
    {

        $user = factory(App\User::class)->create();
        $categoryId = factory(App\DatasetCategory::class)->create()->id;
        $subcategoryId = factory(App\DatasetSubcategory::class)->create()->id;

        $requestData = [
            'dataset' => [
                'name' => 'New Dataset',
                'description' => 'New dataset description.',
                'categoryId' => $categoryId,
                'subcategoryId' => $subcategoryId
            ],
            'source' => [
                'name' => 'New Source',
                'description' => 'New source description.'
            ],
            'entityKey' => [
                'USA', 'Australia', 'New Entity'
            ],
            'entities' => [
                0, 1, 2
            ],
            'years' => [
                1990, 2000, 2010
            ],
            'variables' => [
                [ 
                  'name' => 'New Variable', 
                  'description' => 'New variable description.',
                  'unit' => '%',
                  'typeId' => 3,
                  'values' => [10, 20, 30]
                ],
                [
                  'name' => 'Second Variable', 
                  'description' => 'Second variable description.',
                  'unit' => '%',
                  'typeId' => 3,
                  'values' => [9, 18, 27]
                ]

            ]
        ];

        $result = $this->actingAs($user)
    		 ->post('/import/variables', $requestData)
             ->see('datasetId');

        $this->seeInDatabase('datasets', [
            'name' => 'New Dataset',
            'description' => 'New dataset description.',
            'fk_dst_cat_id' => $categoryId,
            'fk_dst_subcat_id' => $subcategoryId
        ]);

        $this->seeInDatabase('datasources', [
            'name' => 'New Source',
            'description' => 'New source description.'
        ]);

        $this->seeInDatabase('entities', [
            'name' => 'New Entity'
        ]);

        $this->dontSeeInDatabase('entities', [
            'name' => 'USA'
        ]);

        $this->seeInDatabase('variables', [
            'name' => 'New Variable',
            'description' => 'New variable description.',
            'unit' => '%'
        ]);

        $this->seeInDatabase('variables', [
            'name' => 'Second Variable',
            'description' => 'Second variable description.',
            'unit' => '%'
        ]);

        $this->seeInDatabase('data_values', [
            'year' => 1990,
            'value' => 9
        ]);

        $this->seeInDatabase('data_values', [
            'year' => 1990,
            'value' => 10
        ]);

        $this->seeInDatabase('data_values', [
            'year' => 2000,
            'value' => 20
        ]);
    }
}