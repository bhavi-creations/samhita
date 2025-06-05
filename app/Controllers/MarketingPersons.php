<?php

namespace App\Controllers;

use App\Models\MarketingPersonModel;

class MarketingPersons extends BaseController
{
    protected $personModel;

    public function __construct()
    {
        $this->personModel = new MarketingPersonModel();
    }

    public function index()
    {
        $data['persons'] = $this->personModel->findAll();
        return view('marketing_persons/index', $data);
    }

    public function create()
    {
        return view('marketing_persons/create');
    }

   public function store()
{
    // Use the centralized method in the model
    $customId = $this->personModel->generateCustomId();

    $this->personModel->save([
        'custom_id' => $customId,
        'name'      => $this->request->getPost('name'),
        'phone'     => $this->request->getPost('phone'),
        'email'     => $this->request->getPost('email'),
        'address'   => $this->request->getPost('address'),
    ]);

    return redirect()->to('/marketing-persons')->with('success', 'Marketing person added.');
}

    public function edit($id)
    {
        $data['person'] = $this->personModel->find($id);
        return view('marketing_persons/edit', $data);
    }

    public function update($id)
    {
        $this->personModel->update($id, [
            'name' => $this->request->getPost('name'),
            'phone' => $this->request->getPost('phone'),
            'email' => $this->request->getPost('email'),
            'address' => $this->request->getPost('address'),
        ]);

        return redirect()->to('/marketing-persons')->with('success', 'Marketing person updated.');
    }

    public function delete($id)
    {
        $this->personModel->delete($id);
        return redirect()->to('/marketing-persons')->with('success', 'Marketing person deleted.');
    }
}
