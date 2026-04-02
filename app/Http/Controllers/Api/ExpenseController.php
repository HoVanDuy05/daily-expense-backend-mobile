<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class ExpenseController extends Controller
{
    /**
     * Lấy danh sách chi tiêu (Feed chính)
     */
    public function index(Request $request)
    {
        $expenses = Expense::with('category')
            ->where('user_id', $request->user()->id)
            ->orderByDesc('expense_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(fn($e) => [
                'id'            => (string)$e->id,
                'title'         => $e->title ?? ($e->category?->name ?? 'Chi tiêu'),
                'amount'        => -(float) $e->amount,
                'category'      => $e->category?->name ?? 'Khác',
                'image'         => $e->photo_path ? Storage::url($e->photo_path) : null,
                'note'          => $e->note,
                'date'          => Carbon::parse($e->expense_date)->format('H:i, d/m/Y'),
                'expense_date'  => Carbon::parse($e->expense_date)->toDateString(),
            ]);

        return response()->json($expenses);
    }

    /**
     * Lấy thống kê chi tiêu theo danh mục (cho StatisticsScreen)
     */
    public function stats(Request $request)
    {
        $userId = $request->user()->id;
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $stats = Expense::where('user_id', $userId)
            ->whereMonth('expense_date', $month)
            ->whereYear('expense_date', $year)
            ->join('categories', 'expenses.category_id', '=', 'categories.id')
            ->select('categories.name as label', DB::raw('SUM(amount) as amount'))
            ->groupBy('categories.name')
            ->get();

        $total = $stats->sum('amount');

        $formattedStats = $stats->map(fn($s) => [
            'label'   => $s->label,
            'amount'  => (float)$s->amount,
            'percent' => $total > 0 ? round(($s->amount / $total) * 100, 1) : 0,
            'color'   => $this->getColorForCategory($s->label),
        ]);

        return response()->json([
            'total' => (float)$total,
            'categories' => $formattedStats
        ]);
    }

    /**
     * Thêm chi tiêu mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount'      => 'required|numeric',
            'title'       => 'nullable|string|max:100',
            'category_id' => 'required|exists:categories,id',
            'note'        => 'nullable|string',
            'photo'       => 'nullable|image|max:10240',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $uploadedFile = Cloudinary::upload($request->file('photo')->getRealPath(), [
                'folder' => 'daily-expense'
            ]);
            $photoPath = $uploadedFile->getSecurePath();
        }

        $expense = Expense::create([
            'user_id'      => $request->user()->id,
            'category_id'  => $request->category_id,
            'amount'       => abs($request->amount), // Ensure positive in DB
            'title'        => $request->title,
            'note'         => $request->note,
            'photo_path'   => $photoPath,
            'expense_date' => now(),
        ]);

        $expense = $expense->load('category');
        return response()->json([
            'id'           => (string)$expense->id,
            'title'        => $expense->title ?? ($expense->category?->name ?? 'Chi tiêu'),
            'amount'       => -(float)$expense->amount,
            'category'     => $expense->category?->name ?? 'Khác',
            'image'        => $expense->photo_path,
            'note'         => $expense->note,
            'date'         => Carbon::parse($expense->expense_date)->format('H:i, d/m/Y'),
            'expense_date' => Carbon::parse($expense->expense_date)->toDateString(),
        ], 201);
    }

    private function getColorForCategory($label)
    {
        $colors = [
            'Ăn uống'   => '#FF6B6B',
            'Di chuyển' => '#FF9800',
            'Mua sắm'   => '#2196F3',
            'Giải trí'  => '#9C27B0',
            'Khác'      => '#64748B'
        ];
        return $colors[$label] ?? '#64748B';
    }
}
