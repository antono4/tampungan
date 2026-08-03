```markdown
# tampungan Development Patterns

> Auto-generated skill from repository analysis

## Overview
This skill teaches you the core development patterns and conventions used in the `tampungan` TypeScript codebase. You'll learn about file naming, import/export styles, commit message practices, and how to write and run tests. This guide is designed to help you contribute effectively and maintain consistency throughout the project.

## Coding Conventions

### File Naming
- Use **camelCase** for file names.
  - Example: `userProfile.ts`, `dataStore.ts`

### Import Style
- Use **relative imports** for modules within the project.
  - Example:
    ```typescript
    import { fetchData } from './apiClient';
    ```

### Export Style
- Use **named exports** for functions, types, and constants.
  - Example:
    ```typescript
    // In userProfile.ts
    export function getUserProfile(id: string) { ... }
    export const DEFAULT_AVATAR = 'avatar.png';
    ```

### Commit Messages
- Commit messages are **freeform** (no enforced prefixes).
- Average message length is concise (~34 characters).
  - Example:  
    ```
    add user profile fetch logic
    ```

## Workflows

### Adding a New Module
**Trigger:** When you need to add new functionality.
**Command:** `/add-module`

1. Create a new file using camelCase (e.g., `newFeature.ts`).
2. Write your logic using named exports.
3. Import dependencies using relative paths.
4. Add corresponding test file as `newFeature.test.ts`.
5. Commit your changes with a clear, concise message.

### Writing and Running Tests
**Trigger:** When you add or update code.
**Command:** `/run-tests`

1. Create or update a test file matching `*.test.*` (e.g., `dataStore.test.ts`).
2. Write tests for each exported function.
3. Use the project's preferred (unknown) testing framework.
4. Run tests using the project's test command (consult project documentation or package.json).

## Testing Patterns

- Test files are named with the pattern `*.test.*` (e.g., `userProfile.test.ts`).
- Each test file should cover the exported functions from its corresponding module.
- The specific testing framework is not detected—check project dependencies for details.
- Example test file structure:
  ```typescript
  // userProfile.test.ts
  import { getUserProfile } from './userProfile';

  describe('getUserProfile', () => {
    it('returns user data for valid id', () => {
      // test implementation
    });
  });
  ```

## Commands
| Command        | Purpose                                 |
|----------------|-----------------------------------------|
| /add-module    | Scaffold a new module with tests        |
| /run-tests     | Run all test files in the codebase      |
```
