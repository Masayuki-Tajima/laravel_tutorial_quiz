<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Option;
use App\Models\Quiz;
use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     *  クイズ新規登録画面表示
     */
    public function create(Request $request, int $categoryId)
    {
        return view('admin.quizzes.create', [
            'categoryId' => $categoryId
        ]);
    }

    /**
     * クイズ新規登録処理
     */
    public function store(StoreQuizRequest $request, int $categoryId)
    {
        // dd($categoryId, $request);
        //先にクイズを登録
        $quiz = new Quiz();
        $quiz->category_id = $categoryId;
        $quiz->question    = $request->question;
        $quiz->explanation = $request->explanation;
        $quiz->save();

        //クイズIDをもとに、選択肢も登録する
        $options = [
            ['quiz_id' => $quiz->id, 'content' => $request->content1, 'is_correct' => $request->isCorrect1],
            ['quiz_id' => $quiz->id, 'content' => $request->content2, 'is_correct' => $request->isCorrect2],
            ['quiz_id' => $quiz->id, 'content' => $request->content3, 'is_correct' => $request->isCorrect3],
            ['quiz_id' => $quiz->id, 'content' => $request->content4, 'is_correct' => $request->isCorrect4],
        ];

        foreach($options as $option){
            $newOption = new Option();
            $newOption->quiz_id    = $option['quiz_id'];
            $newOption->content    = $option['content'];
            $newOption->is_correct = $option['is_correct'];
            $newOption->save();
        }

        // $option1 = new Option();
        // $option1->quiz_id    = $quiz->id;
        // $option1->content    = $request->content1;
        // $option1->is_correct = $request->isCorrect1;
        // $option1->save();

        // $option2 = new Option();
        // $option2->quiz_id    = $quiz->id;
        // $option2->content    = $request->content2;
        // $option2->is_correct = $request->isCorrect2;
        // $option2->save();

        // $option3 = new Option();
        // $option3->quiz_id    = $quiz->id;
        // $option3->content    = $request->content3;
        // $option3->is_correct = $request->isCorrect3;
        // $option3->save();

        // $option4 = new Option();
        // $option4->quiz_id    = $quiz->id;
        // $option4->content    = $request->content4;
        // $option4->is_correct = $request->isCorrect4;
        // $option4->save();

        return redirect()->route('admin.categories.show', ['categoryId' => $categoryId]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Quiz $quiz)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quiz $quiz)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateQuizRequest $request, Quiz $quiz)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quiz $quiz)
    {
        //
    }
}
