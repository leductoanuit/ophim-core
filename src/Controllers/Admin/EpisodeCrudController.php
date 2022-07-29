<?php

namespace Ophim\Core\Controllers\Admin;

use Backpack\CRUD\app\Http\Controllers\CrudController;
use Backpack\CRUD\app\Library\CrudPanel\CrudPanelFacade as CRUD;
use Illuminate\Http\Request;
use Ophim\Core\Models\Episode;

/**
 * Class EpisodeCrudController
 * @package Ophim\Core\Controllers\Admin
 * @property-read \Backpack\CRUD\app\Library\CrudPanel\CrudPanel $crud
 */
class EpisodeCrudController extends CrudController
{
    use \Backpack\CRUD\app\Http\Controllers\Operations\ListOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\CreateOperation {
        store as backpackStore;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\UpdateOperation {
        update as backpackUpdate;
    }
    use \Backpack\CRUD\app\Http\Controllers\Operations\DeleteOperation;
    use \Backpack\CRUD\app\Http\Controllers\Operations\ShowOperation;

    /**
     * Configure the CrudPanel object. Apply settings to all operations.
     *
     * @return void
     */
    public function setup()
    {
        CRUD::setModel(\Ophim\Core\Models\Episode::class);
        CRUD::setRoute(config('backpack.base.route_prefix') . '/episode');
        CRUD::setEntityNameStrings('Episode', 'episodes');
        $this->crud->addButtonFromModelFunction('line', 'open_episode', 'openEpisode', 'beginning');
        $this->crud->denyAccess('create');
        $this->crud->denyAccess('delete');
    }

    /**
     * Define what happens when the List operation is loaded.
     *
     * @see  https://backpackforlaravel.com/docs/crud-operation-list-entries
     * @return void
     */
    protected function setupListOperation()
    {
        $this->authorize('browse', Episode::class);

        /**
         * Columns can be defined using the fluent syntax or array syntax:
         * - CRUD::column('price')->type('number');
         * - CRUD::addColumn(['name' => 'price', 'type' => 'number','tab'=>'Thông tin phim']);
         */
        $this->crud->addClause('where', 'has_report', true);

        CRUD::addColumn([
            'name' => 'movie', 'label' => 'Phim', 'type' => 'relationship',
            'searchLogic' => function ($query, $column, $searchTerm) {
                $query->orWhereHas('movie', function ($movie) use ($searchTerm) {
                    $movie->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('origin_name', 'like', '%' . $searchTerm . '%');
                });
            }
        ]);
        CRUD::addColumn(['name' => 'name', 'label' => 'Tập', 'type' => 'text']);
        CRUD::addColumn(['name' => 'type', 'label' => 'Type', 'type' => 'text']);
    }

    /**
     * Define what happens when the Create operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-create
     * @return void
     */
    protected function setupCreateOperation()
    {
        abort(404);
    }

    /**
     * Define what happens when the Update operation is loaded.
     *
     * @see https://backpackforlaravel.com/docs/crud-operation-update
     * @return void
     */
    protected function setupUpdateOperation()
    {
        $this->authorize('update', $this->crud->entry);

        CRUD::addField(['name' => 'type', 'label' => 'Type', 'type' => 'select_from_array', 'options' => config('ophim.episodes.types')]);
        CRUD::addField(['name' => 'link', 'label' => 'Nguồn phát', 'type' => 'url']);
        CRUD::addField(['name' => 'has_report', 'label' => 'Đánh dấu đang lỗi', 'type' => 'checkbox']);
        CRUD::addField(['name' => 'report_message', 'label' => 'Report message', 'type' => 'textarea']);
    }
}
