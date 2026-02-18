<?php

namespace BeegoodIT\LaravelFeedback\Tests\Unit;

use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use BeegoodIT\LaravelFeedback\Tests\TestCase;
use BeegoodIT\LaravelFeedback\Tests\TestUser;

class FeedbackItemTest extends TestCase
{
    public function test_feedback_item_has_creator_relationship(): void
    {
        $user = $this->createUser();

        $feedbackItem = FeedbackItem::create([
            'subject' => 'Test Subject',
            'description' => 'Test Description',
            'created_by' => $user->id,
            'user_agent' => 'Mozilla/5.0',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertInstanceOf(TestUser::class, $feedbackItem->creator);
        $this->assertEquals($user->id, $feedbackItem->creator->id);
    }

    public function test_feedback_item_requires_subject(): void
    {
        $user = $this->createUser();

        $this->expectException(\Illuminate\Database\QueryException::class);

        FeedbackItem::create([
            'description' => 'Test description',
            'created_by' => $user->id,
        ]);
    }

    public function test_feedback_item_requires_description(): void
    {
        $user = $this->createUser();

        $this->expectException(\Illuminate\Database\QueryException::class);

        FeedbackItem::create([
            'subject' => 'Test subject',
            'created_by' => $user->id,
        ]);
    }

    public function test_feedback_item_stores_metadata(): void
    {
        $user = $this->createUser();

        $feedbackItem = FeedbackItem::create([
            'subject' => 'Test subject',
            'description' => 'Test description',
            'created_by' => $user->id,
            'user_agent' => 'Mozilla/5.0',
            'ip_address' => '127.0.0.1',
        ]);

        $this->assertEquals('Mozilla/5.0', $feedbackItem->user_agent);
        $this->assertEquals('127.0.0.1', $feedbackItem->ip_address);
    }
}
