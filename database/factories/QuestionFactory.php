<?php

namespace Database\Factories;

use App\Models\Answer;
use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Question>
 */
class QuestionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function configure(): static
    {
        return $this->afterCreating(function (Question $question) {
            $question->answers()->saveMany(Answer::factory(3)->create([
                'question_id' => $question->id,
            ]));
            $question->answers()->save(Answer::factory()->create([
                'question_id' => $question->id,
                'is_correct' => true,
            ]));
        });
    }

    public function definition(): array
    {
        return [
            'question_bank_id' => 1,
            'question_content' => fake()->sentence(),
            'teacher_id' => 1,
        ];
    }
}
