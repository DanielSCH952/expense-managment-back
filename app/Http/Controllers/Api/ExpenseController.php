<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use App\Http\Resources\ExpenseResource;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Endpoint para obtener la lista de gastos de un hogar
     */
    public function index(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'household_id' => ['required', 'exists:households,id'],
                'page' => ['sometimes', 'integer', 'min:1']
            ]);
            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $perPage = 10; // Default perPage
            $currentPage = $request->page ?? 1;
            $expenses = Expense::query()
                ->where('household_id', $request->household_id)
                ->latest()
                ->paginate($perPage, ['*'], 'page', $currentPage);

            return ExpenseResource::collection($expenses);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al consultar los gastos'], 500);
        }
    }
    /**
     * Endpoint para guardar un gasto
     */
    public function store(Request $request)
    {
        try {
            $rules = [
                'household_id' => ['required', 'exists:households,id'],
                'category_id' => ['required', 'exists:categories,id'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'description' => ['nullable', 'string', 'max:255'],
                'expense_date' => ['required', 'date'],
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $expense = Expense::create([
                'household_id' => $request->household_id,
                'category_id' => $request->category_id,
                'amount' => $request->amount,
                'description' => $request->description,
                'expense_date' => $request->expense_date,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Gasto creado.',
                'data' => new ExpenseResource($expense)
            ], 201);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al guardar el gasto.'], 500);
        }
    }

    /**
     * Endpoint para consultar el información de un gasto
     */
    public function show(Expense $expense)
    {
        return new ExpenseResource($expense);
    }

    /**
     * Endpoint para actualizar la información de un gasto
     */
    public function update(Request $request, Expense $expense)
    {
        try {
            $rules = [
                'category_id' => ['required', 'exists:categories,id'],
                'amount' => ['required', 'numeric', 'min:0.01'],
                'description' => ['nullable', 'string', 'max:255'],
                'expense_date' => ['required', 'date'],
            ];
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
                return response()->json($validator->errors()->first(), 400);

            $expense->update($validator->validated());

            return new ExpenseResource($expense);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Error al actualizar el gasto'], 500);
        }
    }

    /**
     * Endpoint para eliminar el registro de un gasto
     */
    public function destroy(Expense $expense)
    {
        $expense->delete();

        return response()->json([
            'message' => 'Gasto eliminado correctamente'
        ]);
    }
}
