<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Charge;
use App\Models\Patient;
use App\Models\TreatmentRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $charges = Charge::with('patient')
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->patient_id, fn ($q, $id) => $q->where('patient_id', $id))
            ->latest('charge_date')
            ->paginate(15)
            ->withQueryString();

        $totals = [
            'unpaid' => Charge::where('status', 'unpaid')->sum('amount'),
            'paid' => Charge::where('status', 'paid')->sum('amount'),
        ];

        return view('charges.index', compact('charges', 'totals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $treatmentRecords = TreatmentRecord::with('patient')->latest('session_date')->take(50)->get();

        return view('charges.create', compact('patients', 'treatmentRecords'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['recorded_by'] = $request->user()->id;
        $validated['status'] = 'unpaid';

        $charge = Charge::create($validated);

        ActivityLog::record('create', 'charges', "Recorded charge #{$charge->id} (₱{$charge->amount}) for patient #{$charge->patient_id}");

        return redirect()->route('charges.index')->with('success', 'Charge recorded.');
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Charge $charge)
    {
        if ($charge->status !== 'unpaid') {
            return redirect()->route('charges.index')
                ->withErrors(['charge' => 'Settled charges cannot be edited. Create a correction charge instead.']);
        }

        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();

        return view('charges.edit', compact('charge', 'patients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Charge $charge)
    {
        if ($charge->status !== 'unpaid') {
            return redirect()->route('charges.index')
                ->withErrors(['charge' => 'This charge has already been settled. Create a correction charge instead of editing a paid or waived record.']);
        }

        $charge->update($this->validated($request, $charge));

        ActivityLog::record('update', 'charges', "Updated charge #{$charge->id}");

        return redirect()->route('charges.index')->with('success', 'Charge updated.');
    }

    public function markPaid(Charge $charge)
    {
        if ($charge->status !== 'unpaid') {
            return back()->withErrors(['charge' => 'Only unpaid charges can be marked as paid.']);
        }

        $charge->update(['status' => 'paid']);

        ActivityLog::record('mark-paid', 'charges', "Marked charge #{$charge->id} as paid");

        return back()->with('success', 'Charge marked as paid.');
    }

    public function waive(Charge $charge)
    {
        if ($charge->status !== 'unpaid') {
            return back()->withErrors(['charge' => 'Only unpaid charges can be waived.']);
        }

        $charge->update(['status' => 'waived']);

        ActivityLog::record('waive', 'charges', "Waived charge #{$charge->id}");

        return back()->with('success', 'Charge waived.');
    }

    protected function validated(Request $request, ?Charge $charge = null): array
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => [
                'required',
                'exists:patients,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $patient = Patient::find($value);

                    if ($patient && $patient->status !== 'active') {
                        $fail('Only active patients can have charges recorded.');
                    }
                },
            ],
            'treatment_record_id' => ['nullable', 'exists:treatment_records,id'],
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'charge_date' => 'required|date',
        ]);

        $validator->after(function ($validator) use ($request, $charge) {
            $patientId = $request->input('patient_id');
            $treatmentRecordId = $request->input('treatment_record_id');

            if ($treatmentRecordId) {
                $treatmentRecord = TreatmentRecord::find($treatmentRecordId);

                if ($treatmentRecord && (int) $treatmentRecord->patient_id !== (int) $patientId) {
                    $validator->errors()->add('patient_id', 'The selected treatment record belongs to a different patient.');
                }
            }

            if ($charge && $charge->status !== 'unpaid') {
                $validator->errors()->add('charge', 'This charge has already been settled. Use a correction workflow instead of editing it.');
            }
        });

        $validator->validate();

        return $validator->validated();
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
