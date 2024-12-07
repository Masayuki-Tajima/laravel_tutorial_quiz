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
        //セッションの解除
        session()->forget('resultArray');

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

        //セッションに保存されているクイズidの配列を取得
        $resultArray = session('resultArray');
        //初回アクセス時は、新たにクイズidの配列を作成
        if (is_null($resultArray)) {
            //クイズidをすべて抽出
            $quizIds = $category->quizzes->pluck('id')->toArray();
            //クイズidの配列をランダムに入れ替える
            shuffle($quizIds);
            $resultArray = [];
            foreach ($quizIds as $quizId) {
                $resultArray[] = [
                    'quizId' => $quizId,
                    'result' => null,
                ];
            }

            session(['resultArray' => $resultArray]);
        }

        //$resultArrayの中で、resultがnullのもののうち最初のデータを選ぶ
        $noAnswerResult = collect($resultArray)->filter(function ($item) {
            return $item['result'] === null;
        })->first();

        if (!$noAnswerResult) {
            dd('未回答のクイズはなくなりました');
        }

        //クイズidに紐づくクイズを取得
        $quiz = $category->quizzes->firstWhere('id', $noAnswerResult['quizId'])->toArray();

        return view('play.quizzes', [
            'categoryId' => $categoryId,
            'quiz' => $quiz,
        ]);
    }

    //クイズ解答画面表示
    public function answer(Request $request, int $categoryId)
    {
        $quizId = $request->quizId;
        $selectedOptions = $request->optionId === null ? [] : $request->optionId;

        $category = Category::with('quizzes.options')->findOrFail($categoryId);
        $quiz = $category->quizzes->firstWhere('id', $quizId);
        $quizOptions = $quiz->options->toArray();
        $isCorrectAnswer = $this->isCorrectAnswer($selectedOptions, $quizOptions);

        //セッションからクイズidと回答情報を取得
        $resultArray = session('resultArray');
        //回答結果をセッションに保存する
        foreach($resultArray as $index => $result){
            if($result['quizId'] === (int)$quizId){
                $resultArray[$index]['result'] = $isCorrectAnswer;
                break;
            }
        }
        //  回答結果をセッションに上書きする
        session(['resultArray' => $resultArray]);

        return view('play.answer', [
            'isCorrectAnswer' => $isCorrectAnswer,
            'quiz'            => $quiz->toArray(),
            'quizOptions'     => $quizOptions,
            'selectedOptions' => $selectedOptions,
            'categoryId'      => $categoryId,
        ]);
    }

    //プレイヤーの解答が正解か不正解かを判定
    private function isCorrectAnswer(array $selectedOptions, array $quizOptions)
    {
        //クイズの選択肢から正解の選択肢を抽出し、そのIdをすべて取得する
        $correctOptions = array_filter($quizOptions, function ($option) {
            return $option['is_correct'] === 1;
        });

        //idの数字だけを抽出する
        $correctOptionIds = array_map(function ($option) {
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
