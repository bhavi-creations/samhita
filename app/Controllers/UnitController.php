<?php

namespace App\Controllers;

use App\Models\UnitModel;

class UnitController extends BaseController
{
    public function index()
    {
        $model = new UnitModel();
        $data['units'] = $model->findAll();
        return view('units/index', $data);
    }

    public function create()
    {
        return view('units/create');
    }

    public function store()
    {
        $model = new UnitModel();
        $model->save([
            'name' => $this->request->getPost('name')
        ]);
        return redirect()->to('/units');
    }

    public function edit($id)
    {
        $model = new UnitModel();
        $data['unit'] = $model->find($id);
        return view('units/edit', $data);
    }

    public function update($id)
    {
        $model = new UnitModel();
        $model->update($id, ['name' => $this->request->getPost('name')]);
        return redirect()->to('/units');
    }

    public function delete($id)
    {
        $model = new UnitModel();
        $model->delete($id);
        return redirect()->to('/units');
    }
}
