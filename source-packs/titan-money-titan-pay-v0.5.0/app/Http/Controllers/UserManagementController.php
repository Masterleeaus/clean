<?php

namespace App\Http\Controllers;

use App\Domains\Company\Models\CompanyMembership;
use App\Domains\Company\Support\CurrentCompany;
use App\Http\Requests\ImportUsersRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;
use Throwable;

class UserManagementController extends Controller
{
    public function index(): View
    {
        $companyId = $this->companyId();
        $users = User::query()
            ->whereHas('companyMemberships', fn ($query) => $query
                ->where('company_id', $companyId)
                ->where('is_active', true))
            ->with(['companyMemberships' => fn ($query) => $query->where('company_id', $companyId)])
            ->latest()
            ->paginate(15);

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        return view('users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->letters()->numbers()->symbols()],
        ]);

        DB::transaction(function () use ($validated): void {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
            ]);

            CompanyMembership::query()->create([
                'company_id' => $this->companyId(),
                'user_id' => $user->id,
                'role' => 'member',
                'permissions_json' => [],
                'is_active' => true,
            ]);
        });

        return redirect()->route('users.index')->with('success', 'Company member created successfully.');
    }

    public function edit(User $user): View
    {
        $this->assertCompanyMember($user);

        return view('users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->assertCompanyMember($user);

        if ($user->companyMemberships()->where('is_active', true)->count() > 1) {
            return back()->withErrors([
                'email' => 'This account belongs to more than one company. Its global identity must be changed by the account owner.',
            ]);
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::min(12)->mixedCase()->letters()->numbers()->symbols()],
        ]);

        $data = ['name' => $validated['name'], 'email' => $validated['email']];
        if (filled($validated['password'] ?? null)) {
            $data['password'] = Hash::make($validated['password']);
        }
        $user->update($data);

        return redirect()->route('users.index')->with('success', 'Company member updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $membership = $this->assertCompanyMember($user);

        if ((int) $user->id === (int) auth()->id()) {
            return back()->withErrors(['error' => 'You cannot remove your own company membership.']);
        }

        if ($membership->role === 'owner') {
            $ownerCount = CompanyMembership::query()
                ->where('company_id', $this->companyId())
                ->where('role', 'owner')
                ->where('is_active', true)
                ->count();
            if ($ownerCount <= 1) {
                return back()->withErrors(['error' => 'The last company owner cannot be removed.']);
            }
        }

        DB::transaction(function () use ($membership, $user): void {
            $membership->delete();
            if ((string) $user->active_company_id === $this->companyId()) {
                $user->forceFill(['active_company_id' => null])->saveQuietly();
            }
        });

        return redirect()->route('users.index')->with('success', 'Company membership removed. The user account and other company memberships were preserved.');
    }

    public function import(ImportUsersRequest $request): RedirectResponse
    {
        $handle = fopen($request->file('csv_file')->getRealPath(), 'rb');
        if ($handle === false) {
            return back()->withErrors(['csv_file' => 'Unable to read the uploaded CSV file.']);
        }

        $header = fgetcsv($handle);
        if ($header !== ['name', 'email', 'password']) {
            fclose($handle);
            return back()->withErrors(['csv_file' => 'Invalid CSV format. Expected columns: name, email, password']);
        }

        $success = 0;
        $failed = 0;
        $skipped = 0;
        $errors = [];
        $line = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $line++;
            if ($row === [null] || $row === []) {
                continue;
            }
            if (count($row) !== 3) {
                $errors[] = "Line {$line}: Invalid number of columns";
                $failed++;
                continue;
            }

            [$name, $email, $password] = array_map('trim', $row);
            if ($name === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
                $errors[] = "Line {$line}: Name, valid email and a password of at least 12 characters are required";
                $failed++;
                continue;
            }
            if (User::query()->where('email', $email)->exists()) {
                $errors[] = "Line {$line}: Email already exists ({$email})";
                $skipped++;
                continue;
            }

            try {
                DB::transaction(function () use ($name, $email, $password): void {
                    $user = User::query()->create([
                        'name' => $name,
                        'email' => $email,
                        'password' => Hash::make($password),
                    ]);
                    CompanyMembership::query()->create([
                        'company_id' => $this->companyId(),
                        'user_id' => $user->id,
                        'role' => 'member',
                        'permissions_json' => [],
                        'is_active' => true,
                    ]);
                });
                $success++;
            } catch (Throwable $exception) {
                Log::warning('Company member CSV import failed.', [
                    'line' => $line,
                    'exception' => $exception::class,
                ]);
                $errors[] = "Line {$line}: The user could not be created";
                $failed++;
            }
        }
        fclose($handle);

        $message = "Import completed: {$success} users created, {$skipped} skipped, {$failed} failed";

        return redirect()->route('users.index')
            ->with('success', $message)
            ->with('import_errors', $errors);
    }

    private function companyId(): string
    {
        return (string) app(CurrentCompany::class)->require()->id;
    }

    private function assertCompanyMember(User $user): CompanyMembership
    {
        return CompanyMembership::query()
            ->where('company_id', $this->companyId())
            ->where('user_id', $user->id)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
