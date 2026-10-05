<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\Journal;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use RuntimeException;

class JournalController extends Controller
{
    public function index(Request $request)
    {
        $journals = Journal::with('creator')
            ->when($request->q, fn ($q) => $q->where('number', 'like', '%'.$request->q.'%')->orWhere('description', 'like', '%'.$request->q.'%'))
            ->latest()->paginate(15)->withQueryString();

        return view('admin.journals.index', compact('journals'));
    }

    public function create()
    {
        return view('admin.journals.form', ['accounts' => Account::query()->where('is_active', true)->orderBy('code')->get()]);
    }

    public function store(Request $request, AccountingService $accounting)
    {
        $data = $request->validate([
            'journal_date' => ['required', 'date'],
            'description' => ['required', 'string', 'max:255'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:accounts,id'],
            'lines.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lines.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['lines'] = collect($data['lines'])->filter(fn ($l) => ! empty($l['account_id']) && (((float) ($l['debit'] ?? 0)) + ((float) ($l['credit'] ?? 0)) > 0))->values()->all();
        if (count($data['lines']) < 2) {
            return back()->withInput()->with('error', 'أدخل سطرين على الأقل.');
        }
        try {
            $journal = $accounting->postJournal($data['description'], $data['lines'], null, $data['journal_date']);
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('journals.show', $journal)->with('success', 'تم ترحيل القيد.');
    }

    public function show(Journal $journal)
    {
        return view('admin.journals.show', ['journal' => $journal->load('lines.account', 'creator')]);
    }
}
