<?php

namespace App\Http\Controllers;

use App\Models\AllowedDomain;
use App\Models\Form;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class FormController extends Controller
{
    public function createForm(Request $request)
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $rules = [
            'name' => 'required',
            'slug' => 'required|unique:forms,slug|regex:/^[a-zA-Z0-9\-\.]+$/',
            'description' => 'required',
            'limit_one_response' => 'required',
            'allowed_domains' => 'array'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid fields',
                'errors' => $validator->errors()
            ], 422);
        }

        $form = Form::create([
            'name' => $request->name,
            'slug' => $request->slug,
            'description' => $request->description,
            'limit_one_response' => $request->limit_one_response,
            'creator_id' => $user->id
        ]);

        foreach ($request->allowed_domains as $dm) {
            AllowedDomain::create([
                'form_id' => $form->id,
                'domain' => $dm
            ]);
        }

        return response()->json([
            'message' => 'Create form success',
            'form' => $form
        ]);
    }

    public function getAllForm()
    {
        $user = Auth::guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $forms = Form::where('creator_id', $user->id)->get();

        return response()->json(['forms' => $forms]);
    }

    public function getDetailForm($slug)
    {
        $user = auth()->guard('sanctum')->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $form = Form::where('slug', $slug)->with(['questions', 'respondens.responden'])->withCount('respondens')->first();

        if (!$form) {
            return response()->json([
                'message' => 'Form not found'
            ], 404);
        }

        $form->is_creator = true;
        if ($form->creator_id != $user->id) {
            $form->is_creator = false;
        }
        $can_access = false;
        foreach ($form->domains as $dom) {
            $domains[] = $dom->domain;
            if ($dom->domain == explode('@', $user->email)[1]) $can_access = true;
        }

        if (!$can_access) return response()->json([
            'message' => 'Forbidden access'
        ], 403);
        $form->allowed_domains = $domains;


        // $respondens = [];
        // foreach ($form->respondens as $res) {
        //     $respondens[] = [
        //         'name' => $res->responden->name,
        //         'email' => $res->responden->email
        //     ];
        // }
        // unset($form->respondens);
        // $form->respondens = $respondens;

        return response()->json(['form' => $form]);
    }
}
