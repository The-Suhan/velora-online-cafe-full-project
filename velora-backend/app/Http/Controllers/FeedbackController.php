<?php

namespace App\Http\Controllers;

use App\Models\Feedback;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    // ── STATS ─────────────────────────────────────────────────
    // GET /api/admin/feedback/stats
    // Kullanım: feedback.vue → üst stat kartları (Total / Pending / Resolved)

    public function stats(): JsonResponse
    {
        $total = Feedback::count();
        $pending = Feedback::where('is_done', false)->count();
        $resolved = Feedback::where('is_done', true)->count();

        $resolutionRate = $total > 0
            ? round(($resolved / $total) * 100, 1)
            : 0;

        // Bu ay / geçen ay karşılaştırması (Total Feedback growth)
        $thisMonth = Feedback::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $lastMonth = Feedback::whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->count();

        $growth = $lastMonth === 0
            ? ($thisMonth > 0 ? 100 : 0)
            : round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);

        return response()->json([
            'total' => $total,
            'pending' => $pending,
            'resolved' => $resolved,
            'resolution_rate' => $resolutionRate,
            'growth' => $growth,        // "+12% from last month" gibi
        ]);
    }

    // ── LIST (feedback.vue ana tablo) ─────────────────────────
    // GET /api/admin/feedback
    //   ?search=        → user adı veya email'de arama
    //   ?type=          → complaint | request | question
    //   ?status=        → pending | resolved   (boş = tümü)
    //   ?page=1
    //   ?per_page=10

    public function index(Request $request): JsonResponse
    {
        $query = Feedback::with([
            'user:id,name,email,profile_photo_path',
            'resolver:id,name',
        ]);

        // Arama — user adı veya email
        if ($search = $request->query('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        // Type filtresi (Complaints / Requests / Questions tab'ları)
        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        // Status filtresi (All Status dropdown)
        if ($status = $request->query('status')) {
            $query->where('is_done', $status === 'resolved');
        }

        $perPage = (int) $request->query('per_page', 10);
        $feedbacks = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($f) => $this->formatFeedback($f));

        return response()->json($feedbacks);
    }

    // ── LIST PENDING (pending.vue) ────────────────────────────
    // GET /api/admin/feedback/pending
    //   ?search=
    //   ?type=
    //   ?page=1
    //   ?per_page=10

    public function pending(Request $request): JsonResponse
    {
        $query = Feedback::with([
            'user:id,name,email,profile_photo_path',
        ])->where('is_done', false);

        if ($search = $request->query('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $perPage = (int) $request->query('per_page', 10);
        $feedbacks = $query
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($f) => $this->formatFeedback($f));

        return response()->json($feedbacks);
    }

    // ── LIST RESOLVED (resolved.vue) ──────────────────────────
    // GET /api/admin/feedback/resolved
    //   ?search=
    //   ?type=
    //   ?page=1
    //   ?per_page=10

    public function resolved(Request $request): JsonResponse
    {
        $query = Feedback::with([
            'user:id,name,email,profile_photo_path',
            'resolver:id,name',
        ])->where('is_done', true);

        if ($search = $request->query('search')) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            });
        }

        if ($type = $request->query('type')) {
            $query->where('type', $type);
        }

        $perPage = (int) $request->query('per_page', 10);
        $feedbacks = $query
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->through(fn ($f) => $this->formatFeedback($f));

        return response()->json($feedbacks);
    }

    // ── SHOW ──────────────────────────────────────────────────
    // GET /api/admin/feedback/{id}

    public function show(Feedback $feedback): JsonResponse
    {
        $feedback->load([
            'user:id,name,email,profile_photo_path',
            'resolver:id,name',
        ]);

        return response()->json($this->formatFeedback($feedback, detail: true));
    }

    // ── MARK AS DONE ──────────────────────────────────────────
    // PATCH /api/admin/feedback/{id}/resolve
    // Body: {}  (auth user otomatik done_by olur)

    public function resolve(Feedback $feedback, Request $request): JsonResponse
    {
        if ($feedback->is_done) {
            return response()->json([
                'message' => 'This feedback is already resolved.',
            ], 422);
        }

        $feedback->update([
            'is_done' => true,
            'done_by' => $request->user()->id,
        ]);

        return response()->json([
            'id' => $feedback->id,
            'is_done' => true,
            'done_by' => $request->user()->name,
            'updated_at' => $feedback->updated_at?->format('M d, Y'),
            'message' => 'Feedback marked as resolved.',
        ]);
    }

    // ── MARK AS PENDING (geri al) ─────────────────────────────
    // PATCH /api/admin/feedback/{id}/unresolve

    public function unresolve(Feedback $feedback): JsonResponse
    {
        if (! $feedback->is_done) {
            return response()->json([
                'message' => 'This feedback is already pending.',
            ], 422);
        }

        $feedback->update([
            'is_done' => false,
            'done_by' => null,
        ]);

        return response()->json([
            'id' => $feedback->id,
            'is_done' => false,
            'message' => 'Feedback reopened as pending.',
        ]);
    }

    // ── DELETE ────────────────────────────────────────────────
    // DELETE /api/admin/feedback/{id}
    // Kullanım: feedback.vue / resolved.vue → sil butonu

    public function destroy(Feedback $feedback): JsonResponse
    {
        $feedback->delete();

        return response()->json([
            'message' => 'Feedback deleted successfully.',
        ]);
    }

    // ── PRIVATE HELPERS ───────────────────────────────────────

    private function formatFeedback(Feedback $f, bool $detail = false): array
    {
        $base = [
            'id' => $f->id,
            'type' => $f->type,                          // complaint | request | question
            'subject' => $f->subject,
            'message' => $detail
                ? $f->message                              // detail modal → tam mesaj
                : mb_substr($f->message, 0, 60).'...',    // tablo → preview
            'is_done' => $f->is_done,
            'status' => $f->is_done ? 'resolved' : 'pending',
            'user' => $f->user ? [
                'id' => $f->user->id,
                'name' => $f->user->name,
                'email' => $f->user->email,
                'avatar' => $f->user->profile_photo_path,
            ] : null,
            'created_at' => $f->created_at?->format('M d, Y'),
        ];

        if ($detail || $f->is_done) {
            $base['resolved_by'] = $f->resolver
                ? ['id' => $f->resolver->id, 'name' => $f->resolver->name]
                : null;
            $base['resolved_at'] = $f->updated_at?->format('M d, Y');
        }

        return $base;
    }
}
