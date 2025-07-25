<?php

namespace App\Controllers;

use App\Models\GstRateModel;


class GstRates extends BaseController
{
    protected $gstRateModel;

    public function __construct()
    {
        $this->gstRateModel = new GstRateModel();
    }

    // Displays a list of all GST rates
    public function index()
    {
        $data = [
            'gstRates' => $this->gstRateModel->findAll(),
            'title'    => 'Manage GST Rates'
        ];
        return view('gst_rates/index', $data);
    }

    // Shows the form to create a new GST rate
    public function create()
    {
        $data['title'] = 'Add New GST Rate';
        // Pass validation errors back to the form if any
        $data['validation'] = \Config\Services::validation();
        return view('gst_rates/create', $data);
    }

    // Handles the form submission for creating a new GST rate
    public function store()
    {
        $rules = [
            'name' => [
                'rules'  => 'required|min_length[3]|max_length[50]|is_unique[gst_rates.name]',
                'errors' => [
                    'required'   => 'GST Rate Name is required.',
                    'min_length' => 'GST Rate Name must be at least 3 characters long.',
                    'max_length' => 'GST Rate Name cannot exceed 50 characters.',
                    'is_unique'  => 'This GST Rate Name already exists.',
                ],
            ],
            'rate' => [
                // --- CHANGE START ---
                // Validation rule remains the same, allowing input up to 100
                'rules'  => 'required|numeric|greater_than_equal_to[0.01]|less_than_equal_to[100.00]',
                'errors' => [
                    'required'              => 'Rate is required.',
                    'numeric'               => 'Rate must be a number.',
                    'greater_than_equal_to' => 'Rate must be at least 0.01.',
                    'less_than_equal_to'    => 'Rate cannot exceed 100%.',
                ],
                // --- CHANGE END ---
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'name' => $this->request->getPost('name'),
            // --- CHANGE START ---
            // Removed division by 100. Save the rate as entered (e.g., 18)
            'rate' => (float) $this->request->getPost('rate'),
            // --- CHANGE END ---
        ];

        try {
            if ($this->gstRateModel->save($data)) {
                return redirect()->to(base_url('gst-rates'))->with('success', 'GST Rate added successfully!');
            } else {
                return redirect()->back()->withInput()->with('error', 'Failed to add GST Rate.');
            }
        } catch (\ReflectionException $e) {
            return redirect()->back()->withInput()->with('error', 'Failed to add GST Rate: ' . $e->getMessage());
        }
    }

    // Shows the form to edit an existing GST rate
    public function edit($id = null)
    {
        $gstRate = $this->gstRateModel->find($id);

        if (empty($gstRate)) {
            return redirect()->to(base_url('gst-rates'))->with('error', 'GST Rate not found.');
        }

        // --- CHANGE START ---
        // Removed multiplication by 100. The rate is already stored as a percentage.
        // $gstRate['rate'] = $gstRate['rate'] * 100; // REMOVED
        // --- CHANGE END ---

        $data = [
            'gstRate'    => $gstRate,
            'title'      => 'Edit GST Rate',
            'validation' => \Config\Services::validation() // Pass validation service
        ];
        return view('gst_rates/edit', $data);
    }

    // Handles the deletion of a GST rate
    public function delete($id = null)
    {
        if ($this->gstRateModel->delete($id)) {
            return redirect()->to(base_url('gst-rates'))->with('success', 'GST Rate deleted successfully!');
        } else {
            return redirect()->to(base_url('gst-rates'))->with('error', 'Failed to delete GST Rate or it does not exist.');
        }
    }

    public function update($id = null)
    {
        $gstRate = $this->gstRateModel->find($id);

        if (!$gstRate) {
            return redirect()->to(base_url('gst-rates'))->with('error', 'GST Rate not found.');
        }

        $rules = [
            'name' => "required|min_length[2]|max_length[50]|is_unique[gst_rates.name,id,{$id}]",
            // --- CHANGE START ---
            // Validation rule remains the same, allowing input up to 100
            'rate' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]'
            // --- CHANGE END ---
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'id'   => $id,
            'name' => $this->request->getPost('name'),
            // --- CHANGE START ---
            // Removed division by 100. Save the rate as entered (e.g., 18)
            'rate' => (float) str_replace(',', '.', $this->request->getPost('rate'))
            // --- CHANGE END ---
        ];

        if ($this->gstRateModel->save($data)) {
            return redirect()->to(base_url('gst-rates'))->with('success', 'GST Rate updated successfully!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Failed to update GST Rate.');
        }
    }
}
