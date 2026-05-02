<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\ContactFormRequest;
use App\Jobs\SendContactNotificationJob;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Redis;

class ContactController extends Controller
{
    /**
     * POST /api/public/contact
     * Rate limit: 3 submissions per IP per hour.
     */
    public function store(ContactFormRequest $request): JsonResponse
    {
        $ip      = $request->ip();
        $rateKey = 'contact_rate:' . $ip;

        $count = Redis::incr($rateKey);
        if ($count === 1) {
            Redis::expire($rateKey, 3600); // 1-hour window
        }
        if ($count > 3) {
            return response()->json([
                'success' => false,
                'error'   => [
                    'code'    => 'RATE_LIMITED',
                    'message' => 'Too many contact form submissions. Please try again later.',
                ],
            ], 429);
        }

        $contact = Contact::create(array_merge(
            $request->validated(),
            [
                'ip_address' => $ip,
                'status'     => 'unread',
            ]
        ));

        SendContactNotificationJob::dispatch($contact);

        return response()->json([
            'success' => true,
            'message' => 'Your message has been received. We will get back to you soon.',
        ], 201);
    }
}
