{{-- 
Original PHP logic has been removed and should be moved to a controller.
This file should not contain direct cookie manipulation or page redirection.
Instead, logout should use Laravel's built-in Auth system with:

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

And in the controller:
public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
}
--}}