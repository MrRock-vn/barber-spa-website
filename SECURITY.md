# Security Notes

This project is prepared for academic submission with the following security rules:

- Do not commit `.env` or real gateway/mail credentials.
- Use `.env.example` for required environment variable names.
- All state-changing forms use CSRF tokens.
- Database access uses prepared statements through PDO.
- Passwords are stored with PHP password hashing.
- Session cookies are HTTP-only and use `SameSite=Lax`.
- Role protected areas call `Auth::requireRole()`.
- User booking and review actions verify ownership before changing data.
- Public output should be escaped with `e()`.

Files such as `debug-*.php`, `test-*.php`, `fix-test-*.php`, and password reset maintenance scripts must be removed from the final submitted package.
