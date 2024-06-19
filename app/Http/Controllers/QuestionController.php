<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    public function addQuestion(Request $request, $slug)
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

        if ($user->id !== $form->creator_id) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        $rules = [
            'name' => 'required',
            'type' => 'required|in:short answer,paragraph,date,time,multiple choice,dropdown,checkboxes',
            'choices' => 'required_if:type,multiple choice,dropdown,checkboxes',
            'is_required' => 'boolean'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $question = Question::create([
            'name' => $request->name,
            'type' => $request->type,
            'is_required' => $request->is_required,
            'form_id' => $form->id
        ]);

        // if (in_array($request->type, ['multiple choice', 'dropdown', 'checkboxes'])) {
        //     $choices = is_array($request->choices) ? $request->choices : explode(",", $request->choices);
        //     $question->choices = $choices;
        //     $question->save();
        // }

        if (in_array($request->type, ['multiple choice', 'dropdown', 'checkboxes'])) {
            $choices = is_array($request->choices) ? $request->choices : explode(",", $request->choices);
            $choicesString = implode(',', $choices);
            $question->choices = $choicesString ;
            $question->save();
        }

        return response()->json([
            'message' => 'Add question successs',
            'question' => $question
        ]);

    }

    public function removeQuestion($slug, $id)
    {
        $user = Auth::guard('sanctum')->user();

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

        $question = Question::where('id', $id)->first();

        if (!$question) {
            return response()->json([
                'message' => 'Question not found'
            ], 404);
        }

        if ($form->creator_id != $user->id) {
            return response()->json([
                'message' => 'Forbidden access'
            ], 403);
        }

        $question->delete();

        return response()->json(['message' => 'Remove Question Success']);
    }
}
