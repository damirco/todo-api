<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class TaskApiTest extends TestCase
{
    use RefreshDatabase; // Автоматически сбрасывает БД между тестами

    /** @test */
    public function test_it_can_create_a_task_via_api()
    {
        $payload = [
            'title' => 'Тестовая задача',
            'description' => 'Описание задачи',
            'due_date' => '2026-10-25T15:00:00',
            'priority' => 'высокий',
            'category' => 'Работа'
        ];

        $response = $this->postJson('/api/tasks', $payload);

        // Проверяем структуру ответа по ТЗ
        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'message'])
            ->assertJson(['message' => 'Task created successfully']);

        // Проверяем, что в базу данных записались в английские значения enum, а категория создалась отдельно
        $this->assertDatabaseHas('tasks', [
            'title' => 'Тестовая задача',
            'priority' => 'high', // В БД должно быть 'high'
            'status' => 'not_completed',
        ]);

        $this->assertDatabaseHas('categories', [
            'name' => 'Работа'
        ]);
    }

    /** @test */
    public function test_it_can_list_tasks_with_pagination_and_russian_labels()
    {
        // Создаем тестовую категорию
        $category = Category::create(['name' => 'Личное']);

        // Создаем 15 задач через фабрику (чтобы проверить пагинацию по 10 штук)
        Task::factory()->count(15)->create([
            'category_id' => $category->id,
            'priority' => TaskPriority::LOW,
            'status' => TaskStatus::COMPLETED
        ]);

        // Делаем запрос к API списку
        $response = $this->getJson('/api/tasks?per_page=10');

        $response->assertStatus(200)
            ->assertJsonCount(10, 'data') // На первой странице должно быть 10 элементов
            ->assertJsonStructure([
                'data' => [
                    '*' => ['id', 'title', 'description', 'due_date', 'create_date', 'priority', 'category', 'status']
                ],
                'links',
                'meta' // Структура пагинации Laravel Resource
            ]);

        // Проверяем, что интерфейс отдает данные на русском, несмотря на то что в БД они на английском
        $this->assertEquals('низкий', $response->json('data.0.priority'));
        $this->assertEquals('выполнена', $response->json('data.0.status'));
        $this->assertEquals('Личное', $response->json('data.0.category'));
    }

    /** @test */
    public function test_it_can_search_tasks_by_title()
    {
        $category = Category::create(['name' => 'Дом']);

        Task::factory()->create(['title' => 'Купить молоко', 'category_id' => $category->id]);
        Task::factory()->create(['title' => 'Убрать комнату', 'category_id' => $category->id]);

        // Ищем конкретную задачу
        $response = $this->getJson('/api/tasks?search=молоко');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.title', 'Купить молоко');
    }

    /** @test */
    public function test_it_validates_required_fields_on_creation()
    {
        // Отправляем пустой запрос
        $response = $this->postJson('/api/tasks', []);

        // Должна вернуться ошибка валидации 422
        $response->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'due_date', 'priority', 'category']);
    }
}
