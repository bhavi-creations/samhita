<?php

namespace App\Controllers;
use App\Models\UnitModel;

class Units extends BaseController
{
    protected $unitModel;

    public function __construct()
    {
        $this->unitModel = new UnitModel();
    }

    public function index()
    {
        $data['units'] = $this->unitModel->findAll();
        return view('units/index', $data);
    }

    public function create()
    {
        return view('units/create');
    }

    public function store()
    {
        $name = $this->request->getPost('name');
        $this->unitModel->save(['name' => $name]);
        return redirect()->to('/units')->with('success', 'Unit added successfully.');
    }

    public function edit($id)
    {
        $unit = $this->unitModel->find($id);
        if (!$unit) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Unit not found');
        }
        return view('units/edit', ['unit' => $unit]);
    }

    public function update($id)
    {
        $name = $this->request->getPost('name');
        $this->unitModel->update($id, ['name' => $name]);
        return redirect()->to('/units')->with('success', 'Unit updated successfully.');
    }

    public function delete($id)
    {
        $this->unitModel->delete($id);
        return redirect()->to('/units')->with('success', 'Unit deleted successfully.');
    }
}
