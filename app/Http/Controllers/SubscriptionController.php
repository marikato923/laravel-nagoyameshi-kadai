<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SubscriptionController extends Controller
{
    public function create()
    {
        $intent = Auth::user()->createSetupIntent();

        return view('subscription.create', compact('intent'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->subscribed('premium_plan')) {
            return redirect('/')
                    ->with('flash_message', 'すでに有料プランに登録済みです。');
        }

        try {
            $user->newSubscription('premium_plan', 'price_1QUlQtEEOG402gkC2UDm94kG')
                ->create($request->paymentMethodId);

            return redirect('/')
                ->with('flash_message', '有料プランへの登録が完了しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'サブスクリプション登録中にエラーが発生しました:' . $e->getMessage()]);
        }
    }

    public function edit()
    {
        $user = Auth::user();

        $intent = $user->createSetupIntent();

        return view('subscription.edit', [
            'user' => $user,
            'intent' => $intent,
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        try {
            $user->updateDefaultPaymentMethod($request->paymentMethodId);

            return redirect('/')
                ->with('flash_message', 'お支払い方法を変更しました。');
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error' => 'お支払い方法の更新に失敗しました:' . $e->getMessage()]);
        }
    }

    public function cancel()
    {
        $user = Auth::user();

        return view('subscription.cancel', compact('user'));
    }

    public function destroy()
    {
        $user = Auth::user();

        try {
            $user->subscription('premium_plan')->cancelNow();

            return redirect('/')
                    ->with('flash_message', '有料プランを解約しました。');
        } catch (\Exception $e) {
            return back()
                    ->withErrors(['error' => '解約処理に失敗しました:' . $e->getMessage()]);
        }
    }
}