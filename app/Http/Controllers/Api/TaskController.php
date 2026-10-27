<?php

namespace App\Http\Controllers\Api;

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTaskRequest;
use App\Http\Requests\UpdateTaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Category;
use App\Models\Task;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;


#[OA\Info(
    version: "1.0.0",
    description: "REST API для управления списком задач",
    title: "Task Management REST API"
)]
#[OA\Server(
    url: "/api",
    description: "Основной сервер API"
)]
class TaskController extends Controller
{
    #[OA\Get(
        path: "/tasks",
        summary: "Получение списка задач",
        operationId: "getTasksList",
        tags: ["Tasks"]
    )]
    #[OA\Parameter(name: "search", in: "query", required: false, schema: new OA\Schema(type: "string"))]
    #[OA\Parameter(name: "sort", in: "query", required: false, schema: new OA\Schema(type: "string", enum: ["due_date", "created_at"]))]
    #[OA\Parameter(name: "page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 1))]
    #[OA\Parameter(name: "per_page", in: "query", required: false, schema: new OA\Schema(type: "integer", default: 10))]
    #[OA\Response(response: 200, description: "Успешный запрос списка задач")]
    public function index(Request $request)
    {
        $request->validate([
            'per_page' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = Task::with('category');

        // Поиск по названию (если передан параметр search)
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        // Сортировка (безопасная проверка по разрешенным полям)
        $sortField = $request->input('sort');
        if (in_array($sortField, ['due_date', 'created_at'])) {
            $query->orderBy($sortField, 'asc'); // можно сделать 'desc' или динамическим
        } else {
            $query->orderBy('id', 'desc'); // Сортировка по умолчанию
        }

        // Постраничный вывод (например, по 10 элементов на страницу)
        $tasks = $query->paginate($request->input('per_page', 10));

        // Возвращаем коллекцию через ресурс (сохраняет структуру пагинации Laravel)
        return TaskResource::collection($tasks);
    }

    #[OA\Post(
        path: "/tasks",
        summary: "Создание новой задачи",
        operationId: "storeTask",
        tags: ["Tasks"]
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ["title", "due_date", "priority", "category"],
            properties: [
                new OA\Property(property: "title", type: "string", maxLength: 255, example: "Задача1"),
                new OA\Property(property: "description", type: "string", nullable: true, example: "Описание задачи"),
                new OA\Property(property: "due_date", type: "string", format: "date-time", example: "2025-01-20T15:00:00"),
                new OA\Property(property: "priority", type: "string", enum: ["низкий", "средний", "высокий"], example: "высокий"),
                new OA\Property(property: "category", type: "string", example: "Работа"),
                new OA\Property(property: "status", type: "string", enum: ["выполнена", "не выполнена"], default: "не выполнена", example: "не выполнена")
            ]
        )
    )]
    #[OA\Response(response: 201, description: "Задача успешно создана")]
    #[OA\Response(response: 422, description: "Ошибка валидации данных")]
    public function store(StoreTaskRequest $request)
    {
        // Ищем категорию по имени, если нет - создаем новую
        $category = Category::firstOrCreate([
            'name' => $request->category
        ]);

        $data = $request->validated();
        $data['category_id'] = $category->id;
        $data['priority'] = TaskPriority::fromLabel($request->priority);
        if ($request->has('status')) {
            $data['status'] = TaskStatus::fromLabel($request->status);
        }

        $task = Task::create($data);

        return response()->json([
            'id' => $task->id,
            'message' => 'Task created successfully'
        ], 201);
    }

    #[OA\Get(
        path: "/tasks/{id}",
        summary: "Получение конкретной задачи",
        operationId: "getTaskById",
        tags: ["Tasks"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID задачи", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Информация о задаче успешно получена")]
    #[OA\Response(response: 404, description: "Задача не найдена")]
    public function show(Task $task)
    {
        return new TaskResource($task);
    }

    #[OA\Put(
        path: "/tasks/{id}",
        summary: "Обновление существующей задачи",
        operationId: "updateTask",
        tags: ["Tasks"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID задачи", schema: new OA\Schema(type: "integer"))]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "title", type: "string", maxLength: 255, example: "Задача2"),
                new OA\Property(property: "description", type: "string", nullable: true, example: "Обновленное описание"),
                new OA\Property(property: "due_date", type: "string", format: "date-time", example: "2025-01-25T18:00:00"),
                new OA\Property(property: "priority", type: "string", enum: ["низкий", "средний", "высокий"], example: "низкий"),
                new OA\Property(property: "category", type: "string", example: "Личное"),
                new OA\Property(property: "status", type: "string", enum: ["выполнена", "не выполнена"], example: "выполнена")
            ]
        )
    )]
    #[OA\Response(response: 200, description: "Задача успешно обновлена")]
    #[OA\Response(response: 422, description: "Ошибка валидации")]
    #[OA\Response(response: 404, description: "Задача не найдена")]
    public function update(UpdateTaskRequest $request, Task $task)
    {
        $data = $request->validated();

        if ($request->has('category')) {
            $category = Category::firstOrCreate([
                'name' => $request->category
            ]);
            $data['category_id'] = $category->id;
        }
        if ($request->has('priority')) {
            $data['priority'] = TaskPriority::fromLabel($request->priority);
        }
        if ($request->has('status')) {
            $data['status'] = TaskStatus::fromLabel($request->status);
        }

        $task->update($data);

        return response()->json([
            'message' => 'Task updated successfully'
        ]);
    }

    #[OA\Delete(
        path: "/tasks/{id}",
        summary: "Удаление задачи",
        operationId: "deleteTask",
        tags: ["Tasks"]
    )]
    #[OA\Parameter(name: "id", in: "path", required: true, description: "ID задачи", schema: new OA\Schema(type: "integer"))]
    #[OA\Response(response: 200, description: "Задача успешно удалена")]
    #[OA\Response(response: 404, description: "Задача не найдена")]
    public function destroy(Task $task)
    {
        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }
}
