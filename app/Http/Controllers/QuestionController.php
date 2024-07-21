<?php

namespace App\Http\Controllers;

use App\Http\Resources\QuestionResource;
use App\Models\Answer;
use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->question_bank_id) {
            $questions = Question::where('question_bank_id', $request->question_bank_id)->with(['teacher', 'answers'])->orderBy('question_content')->get();
        } else {
            $questions = Question::with(['teacher', 'answers'])->orderBy('question_content')->get();
        }

        return response()->json(QuestionResource::collection($questions));
    }

    public function create(Request $request)
    {
        $image = null;
        if ($request->file('image')) {
            $image = $request->file('image')->store('public');
        }
        $question = Question::create(array_merge($request->only(['question_content', 'question_bank_id']), ['teacher_id' => auth()->id(), 'image' => $image]));
        foreach ($request->answers as $index => $answer) {
            $image = null;
            if ($request->file('answers.' . $index . '.image')) {
                $image = $request->file('answers.' . $index . '.image')->store('public');
            }
            Answer::create([
                'answer_content' => $answer['answer_content'],
                'image' => $image,
                'is_correct' => $answer['is_correct'] ?? false ,
                'question_id' => $question->id
            ]);
        }

        return response()->json(['message' => 'Tạo câu hỏi thành công']);
    }

    public function update(Request $request, $id)
    {
        $question = Question::find($id)->load('answers');
        $question->update($request->only(['question_content', 'question_bank_id']));
        if ($request->file('image')) {
            $question->image = $request->file('image')->store('public');
            $question->save();
        }
        $answerIds = [];
        foreach ($request->answers as $index => $answer) {
            $image = null;
            if ($request->file('answers.' . $index . '.image')) {
                $image = $request->file('answers.' . $index . '.image')->store('public');
            }
            $data = [
                'answer_content' => $answer['answer_content'],
                'is_correct' => $answer['is_correct'] ?? false,
                'question_id' => $question->id
            ];
            if ($image) {
                $data['image'] = $image;
            }
            if (isset($answer['id'])) {
                $answerModel = Answer::firstOrCreate(['id' => $answer['id']], $data);
            } else {
                $answerModel = Answer::create($data);
            }
            $answerIds[] = $answerModel->id;
        }
        Answer::where('question_id', $question->id)->whereNotIn('id', $answerIds)->delete();

        return response()->json(['message' => 'Cập nhật câu hỏi thành công']);
    }

    public function delete($id)
    {
        $question = Question::find($id);
        if ($question) {
            $question->answers()->delete();
            $question->delete();
        }

        return response()->json(['message' => 'Xóa câu hỏi thành công']);
    }
}
