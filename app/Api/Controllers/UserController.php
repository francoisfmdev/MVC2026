<?php

declare(strict_types=1);

namespace App\Api\Controllers;

use App\DTO\UserInputDTO;
use App\DTO\UserOutputDTO;
use App\Models\User;
use Core\ApiController;
use Core\Request;
use Core\ValidationException;

/**
 * CRUD JSON /api/users. Réutilise App\Models\User et les DTO (pas de duplication SQL).
 */
final class UserController extends ApiController
{
    public function index(Request $request): void
    {
        $users = array_map(
            static fn (array $row): array => UserOutputDTO::fromArray($row)->toArray(),
            User::all()
        );

        $this->json(['data' => $users]);
    }

    public function show(Request $request): void
    {
        $user = User::find((int) $request->param('id'));
        if ($user === null) {
            $this->error('Utilisateur introuvable', 404);

            return;
        }

        $this->json(['data' => UserOutputDTO::fromArray($user)->toArray()]);
    }

    public function store(Request $request): void
    {
        try {
            $input = UserInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->json(['error' => 'Validation échouée', 'status' => 422, 'errors' => $e->errors], 422);

            return;
        }

        $id = User::table()->insert([
            'name' => $input->name,
            'email' => $input->email,
        ]);

        $user = User::find($id);
        $this->json(['data' => UserOutputDTO::fromArray($user)->toArray()], 201);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        if (User::find($id) === null) {
            $this->error('Utilisateur introuvable', 404);

            return;
        }

        try {
            $input = UserInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->json(['error' => 'Validation échouée', 'status' => 422, 'errors' => $e->errors], 422);

            return;
        }

        User::table()->where('id', '=', $id)->update([
            'name' => $input->name,
            'email' => $input->email,
        ]);

        $user = User::find($id);
        $this->json(['data' => UserOutputDTO::fromArray($user)->toArray()]);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        if (User::find($id) === null) {
            $this->error('Utilisateur introuvable', 404);

            return;
        }

        User::table()->where('id', '=', $id)->delete();
        $this->json(['deleted' => true], 200);
    }
}
