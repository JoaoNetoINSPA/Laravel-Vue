<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Loan;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoanAPIController extends Controller
{
    public function getIndex(?int $id = null)
    {
        if ($id) {
            return Loan::with('book', 'user')->find($id);
        }

        return Loan::with('book', 'user')->orderBy('id', 'DESC')->get();
    }

    public function postIndex(Request $request) {
        $data = $request->validate([
            'book_id'     => 'required|exists:books,id',
            'email' => 'required|email|exists:users,email',
            'password' => 'required|string',
            //  'user_id'     => 'required|exists:users,id',
            'returned_at' => 'nullable',
        ]);

        /* 
        book_id: this.dialog.form.book_id,
        email: this.dialog.form.email,
        password : this.dialog.form.password,
        returned_at : `${year}-${month}-${day}`,
        */

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The email or password are incorrect.'],
            ]);
        }


        // Add a due_at field with a default value set to 14 days from the loaned_at.

        return Loan::create([
            'book_id'     => $data['book_id'],
            'user_id'     => $user->id,
            'loaned_at'   => now(),
            'returned_at' => $data['returned_at'] ?? null,
        ]);
    }

    public function putIndex(Request $request, int $id) {
        $loan = Loan::find($id);
        if (empty($loan)) {
            throw new Exception('Could not find loan.');
        }

        $data = $request->validate([
            'returned_at' => 'nullable|datetime',
        ]);

        $loan->update($data);
        return $loan;
    }

    public function deleteIndex(int $id) {
        $loan = Loan::find($id);
        if (empty($loan)) {
            throw new Exception('Could not find loan.');
        }

        $loan->delete();

        return response()->noContent();
    }

    public function putExtend(int $id) 
    {
        $loan = Loan::with('book', 'user')->find($id);

        // Validate that the loan is not already overdue.
        if ($loan->due_at->isPast()) {
            return response()->json(["msg" => "already overdue"]);
        }

        $loan->due_at = $loan->due_at->addDays(7);
        $twoWeeks = Carbon::now()->addDays(14);

        // Validate that the additional_days is a positive integer and no more than 2 weeks.
        if ($loan->due_at->greaterThan($twoWeeks)) {
            return response()->json(["msg" => "more than 2 weeks"]);
        }

        $loan->update();

        return $loan;
    }

}
