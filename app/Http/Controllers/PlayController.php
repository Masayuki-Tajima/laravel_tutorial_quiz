<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class PlayController extends Controller
{
    //プレイ画面トップページ
    public function top()
    {
        $categories = Category::all();
        return view('play.top', [
            'categories' => $categories,
        ]);
    }

    //クイズスタート画面
    public function categories(Request $request, int $categoryId)
    {
        $category = Category::withCount('quizzes')->findOrFail($categoryId);
        return view('play.start', [
            'category'     => $category,
            'quizzesCount' => $category->quizzes_count,
        ]);
    }

    //クイズ出題画面
    public function quizzes(Request $request, int $categoryId)
    {
        //カテゴリーに紐づくクイズと選択肢をすべて取得する
        $category = Category::with('quizzes.options')->findOrFail($categoryId);
        //クイズをランダムに選ぶ
        $quizzes = $category->quizzes->toArray();
        shuffle($quizzes);
        $quiz = $quizzes[0];

        return view('play.quizzes', [
            'categoryId' => $categoryId,
            'quiz' => $quiz,
        ]);
    }

    //クイズ解答画面表示
    public function answer(Request $request, int $categoryId)
    {
        $quizId = $request->quizId;
        $selectedOptions = $request->optionId;

        $category = Category::with('quizzes.options')->findOrFail($categoryId);
        $quiz = $category->quizzes->firstWhere('id', $quizId);
        $quizOptions = $quiz->options->toArray();
        $result = $this->isCorrectAnswer($selectedOptions, $quizOptions);
        dd($result);
        return view('play.answer');
    }

    //プレイヤーの解答が正解か不正解かを判定
    private function isCorrectAnswer(array $selectedOptions, array $quizOptions)
    {
        //クイズの選択肢から正解の選択肢を抽出し、そのIdをすべて取得する
        $correctOptions = array_filter($quizOptions, function ($option) {
            return $option['is_correct'] === 1;
        });

        //idの数字だけを抽出する
        $correctOptionIds = array_map(function($option){
            return $option['id'];
        }, $correctOptions);

        //プレイヤーが選んだ選択肢の個数と、正解の選択肢の個数が一致するかを判定する
        if (count($selectedOptions) !== count($correctOptionIds)) {
            return false;
        }

        //プレイヤーが選んだ選択肢のidと正解のidがすべて一致するか判定する
        foreach ($selectedOptions as $selectedOption) {
            if (!in_array((int)$selectedOption, $correctOptionIds)) {
                return false;
            }
        }

        //正解であることを返す
        return true;
    }
}
