<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\ContractProgressItem;
use Illuminate\Http\Request;

class ContractProgressController extends Controller
{
    public function index(Contract $contract)
    {
        $items = $contract->progressItems()->get();

        return view('progress-editor', compact('contract', 'items'));
    }

    public function store(Request $request, Contract $contract)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
        ]);

        $maxOrder = (int) $contract->progressItems()->max('sort_order');

        $contract->progressItems()->create([
            ...$data,
            'sort_order' => $maxOrder + 10,
            'status' => 'todo',
        ]);

        return back()->with('success', 'Progress step added.');
    }

    public function update(Request $request, Contract $contract, ContractProgressItem $item)
    {
        abort_unless($item->contract_id === $contract->id, 404);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weight' => ['required', 'integer', 'min:1', 'max:100'],
            'status' => ['required', 'in:todo,in_progress,done'],
            'started_at' => ['nullable', 'date'],
            'completed_at' => ['nullable', 'date'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        // Auto-set dates (optional, but nice)
        if ($data['status'] === 'done' && empty($data['completed_at'])) {
            $data['completed_at'] = now()->toDateString();
        }
        if ($data['status'] === 'in_progress' && empty($data['started_at'])) {
            $data['started_at'] = now()->toDateString();
        }
        if ($data['status'] === 'todo') {
            $data['started_at'] = null;
            $data['completed_at'] = null;
        }

        $item->update($data);

        return back()->with('success', 'Progress step updated.');
    }

    public function destroy(Contract $contract, ContractProgressItem $item)
    {
        abort_unless($item->contract_id === $contract->id, 404);

        $item->delete();
        return back()->with('success', 'Progress step deleted.');
    }
}