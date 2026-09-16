<?php

declare(strict_types=1);

namespace App\Web\Controllers;

use App\DTO\UserInputDTO;
use App\DTO\UserOutputDTO;
use App\Models\User;
use Core\Controller;
use Core\Request;
use Core\ValidationException;

/**
 * CRUD HTML des utilisateurs (Twig + CSRF + flash).
 * Le JSON équivalent est App\Api\Controllers\UserController — même Model et mêmes DTO.
 */
final class UserController extends Controller
{
    public function index(Request $request): void
    {
        // Tableaux SQL → DTO de sortie (contrat d'affichage explicite).
        $users = array_map(
            static fn (array $row): array => UserOutputDTO::fromArray($row)->toArray(),
            User::all()
        );

        $this->view('users/index.twig', ['users' => $users]);
    }

    public function create(Request $request): void
    {
        $this->view('users/form.twig', [
            'user' => null,
            'action' => $this->view->url('/users'),
        ]);
    }

    public function store(Request $request): void
    {
        try {
            $input = UserInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/users/create');
        }

        User::table()->insert([
            'name' => $input->name,
            'email' => $input->email,
        ]);

        $this->session->flash('success', 'Utilisateur créé.');
        $this->redirect('/users');
    }

    public function show(Request $request): void
    {
        $user = $this->findOrFail((int) $request->param('id'));
        $this->view('users/show.twig', [
            'user' => UserOutputDTO::fromArray($user)->toArray(),
        ]);
    }

    public function edit(Request $request): void
    {
        $user = $this->findOrFail((int) $request->param('id'));
        $this->view('users/form.twig', [
            'user' => $user,
            'action' => $this->view->url('/users/' . $user['id']),
        ]);
    }

    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        $this->findOrFail($id);

        try {
            $input = UserInputDTO::fromArray($request->all());
        } catch (ValidationException $e) {
            $this->session->flash('errors', $e->errors);
            $this->session->flash('old', $request->all());
            $this->redirect('/users/' . $id . '/edit');
        }

        User::table()->where('id', '=', $id)->update([
            'name' => $input->name,
            'email' => $input->email,
        ]);

        $this->session->flash('success', 'Utilisateur mis à jour.');
        $this->redirect('/users/' . $id);
    }

    public function destroy(Request $request): void
    {
        $id = (int) $request->param('id');
        $this->findOrFail($id);
        User::table()->where('id', '=', $id)->delete();
        $this->session->flash('success', 'Utilisateur supprimé.');
        $this->redirect('/users');
    }

    /** @return array<string, mixed> */
    private function findOrFail(int $id): array
    {
        $user = User::find($id);
        if ($user === null) {
            $this->session->flash('error', 'Utilisateur introuvable.');
            $this->redirect('/users');
        }

        return $user;
    }
}
