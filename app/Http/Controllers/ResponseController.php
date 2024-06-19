<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Response;
use App\Models\Answer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    public function submitResponse(Request $request, $slug)
    {
        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unathenticated'
            ], 401);
        }

        $form = Form::where('slug', $slug)->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'answers' => 'required|array',
            'answers.*.question_id' => 'required|exists:questions,id',
            'answers.*.value' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $can_access = false;
        foreach ($form->domains as $dom) {
            if ($dom->domain == explode('@', $user->email)[1]) $can_access = true;
        }

        if (!$can_access) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        if ($form->limit_one_response && $form->respondens()->where('user_id', $user->id)->exists()) {
            return response()->json([
                'message' => 'You can not submit form twice.'
            ], 422);
        }

        $response = Response::create([
            'user_id' => $user->id,
            'form_id' => $form->id,
            'date' => now()
        ]);

        foreach ($request->answers as $answer) {
            Answer::create([
                'response_id' => $response->id,
                'question_id' => $answer['question_id'],
                'value' => $answer['value']
            ]);
        }

        return response()->json(['message' => 'Submit response success']);
    }

    // public function getResponses($slug)
    // {
    //     $user = auth()->guard('sanctum')->user();

    //     if (!$user) {
    //         return response()->json([
    //             'message' => 'Unatuhenticated'
    //         ], 401);
    //     }

    //     $form = Form::where('slug', $slug)->first();

    //     if (!$form) {
    //         return response()->json([
    //             'message' => 'Form not found'
    //         ], 404);
    //     }

    //     $responses = Response::with('user', 'answers')->get();

    //     return response()->json([
    //         'responses' => $responses
    //     ]);
    // }

    public function getResponses($slug)
    {
        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $form = Form::where('slug', $slug)->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }

        if ($form->creator_id != $user->id) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        $responses = Response::where('form_id', $form->id)->with('responden', 'answers.question')->get()->map(function ($response) {
            // Ambil data jawaban untuk setiap respons
            $answers = [];
            foreach ($response->answers as $answer) {
                $questionName = $answer->question->name;
                $answers[$questionName] = $answer->value;
            }

            return [
                'date' => $response->date,
                'user' => [
                    'id' => $response->responden->id,
                    'name' => $response->responden->name,
                    'email' => $response->responden->email
                ],
                'answers' => $answers
            ];
        });

        return response()->json([
            'message' => 'Get response success',
            'responses' => $responses
        ]);
    }
}
