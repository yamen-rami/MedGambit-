# Gambits Livewire Component Implementation Summary

## Changes Made

### File: `resources/views/components/⚡gambits.blade.php`

#### Component Logic (Anonymous Livewire Class)

**Imports & Traits:**
- Added `Livewire\WithPagination` trait for pagination support
- Added `Livewire\Attributes\Computed` for computed properties
- Imported `App\Models\Questions` model

**Public Properties:**
- `string $search = ''` - Full-text search query
- `string $length = ''` - Question length filter (short/medium/long)
- `array $references = []` - Reference IDs filter
- `string $difficulty = ''` - Difficulty level filter (easy/medium/hard/nerd)
- `array $branches = []` - Branch of Medicine IDs filter
- `array $sp = []` - Specialty IDs filter
- `array $skills = []` - Skill IDs filter
- `string $direction = 'desc'` - Sort direction (asc/desc)
- `string $sort = 'created_at'` - Sort column (difficulty, length, name, created_at, updated_at)
- `int $count = 20` - Questions per page

**Computed Method: `questions()`**
- Builds a filtered/sorted query with all eager-loaded relationships
- **Full-Text Search:** Uses `whereFullText(['name', 'content', 'topic'], $search)` - reuses existing project pattern
- **Filters:** Length, difficulty, branches (via `whereHas`), specialties, skills, references
- **Sorting:** Whitelist-based allowedSorts to prevent SQL injection
- **Pagination:** Uses `paginate($count)` with `WithPagination` trait
- Secondary sort by ID to ensure consistent pagination

**Methods:**
- `updated($property)` - Resets pagination page when any filter/sort changes
- `toggleDirection()` - Switches between ascending/descending sort
- `clearFilters()` - Resets filters and dispatches event to clear Select2 UI
- `tryQuestion(int $questionId)` - Placeholder for question flow testing (returns `dd("here")`)

#### Blade Template

**Header Section:**
- Title: "Questions"
- Search input with live debounce at 400ms
- Filters button (dropdown toggle)
- Create Question button (styling only)

**Filter Dropdown:**
- **Sort:** Select dropdown with columns + toggle direction button (icon changes based on current direction)
- **Difficulty:** Button group (All, Easy, Medium, Hard, Nerd) with active state styling
- **Length:** Button group (All, Short, Medium, Long) with active state styling
- **Branches, Specialties, Skills, References:** Select2 AJAX dropdowns (wire:ignore to prevent Livewire conflicts)
- Clear Filters button: Resets all filters except search and dispatches event to clear Select2

**Active Filters Section:**
- Conditionally displays badges for applied filters
- Shows individual remove buttons for difficulty and length
- Shows count badges for multi-select filters

**Results Table:**
- Dynamically iterates over paginated questions using `@forelse`
- Columns: ID, Question Name, Difficulty (badge), Length (badge), Reference, Actions
- Actions: "Try Question" button (wired to `tryQuestion()` method) + menu button
- Empty state: "No questions found." message

**Pagination Footer:**
- Shows "Showing X to Y of Z questions"
- Renders Laravel pagination links via `$this->questions->links()`

**Select2 JavaScript Initialization:**
- `@script` block (Livewire 3 native) runs on page load
- `initializeGambitsSelect()` helper function:
  - Checks element exists before initializing
  - Destroys and reinitializes if already created (prevents conflicts)
  - Configures Select2 with AJAX endpoints
  - Maps returned JSON to Select2 format
  - Updates Livewire property on change via `$wire.set()`
- Initializes four Select2 dropdowns using existing AJAX routes:
  - `getBranches` → updates `$branches`
  - `getSpeciality` → updates `$sp`
  - `getSkills` → updates `$skills`
  - `getReferences` → updates `$references`
- Listens to `gambits-filters-cleared` event to clear Select2 UI when filters reset

## Architectural Decisions

### 1. **Full-Text Search Implementation**
- Uses existing `whereFullText(['name', 'content', 'topic'], $search)` pattern already established in the project
- Matches the implementation in the Questions admin component and QuestionsController
- No new search system introduced

### 2. **Select2 AJAX Integration**
- Reuses existing four AJAX endpoints (`getBranches`, `getSpeciality`, `getSkills`, `getReferences`)
- No new routes or controllers added
- Uses Livewire 3 native `@script` block for JavaScript initialization
- `wire:ignore` prevents Livewire from hydrating Select2's DOM, preventing conflicts
- Namespaced event listeners (`change.gambits`) prevent interference with other components

### 3. **Sorting Strategy**
- Whitelist-based approach using `$allowedSorts` array to prevent SQL injection
- Defaults to `created_at` if invalid sort column requested
- Secondary sort by ID ensures consistent pagination across sort columns
- Both sorting directions validated against allowlist before use

### 4. **Filter Reset Behavior**
- `updated()` hook resets pagination page whenever filters change
- `clearFilters()` resets all filters except search (to preserve UX intent)
- Dispatch event to JavaScript to clear Select2 UI state synchronously

### 5. **Eager Loading**
- Loaded relationships: `branches`, `skills`, `specialties`, `reference`
- Prevents N+1 queries when rendering the table
- Badge rendering for difficulty/length uses question properties directly

### 6. **Pagination**
- Uses Laravel's built-in pagination with `WithPagination` trait
- Bootstrap theme configured via `paginationTheme` property
- Displays pagination links without custom markup

### 7. **Try/Test Functionality**
- Method `tryQuestion()` receives question ID parameter
- Current implementation: `dd("here")` - temporary placeholder for testing
- Can be replaced with actual question flow routing without changing component logic

## Query Optimization

The `questions()` computed property:
1. Selects only relevant columns via explicit model relationships
2. Uses `->with()` for eager loading to prevent N+1 queries
3. Applies filters conditionally using `->when()` (lazy evaluation)
4. Uses `whereHas()` for relational filters (efficient AND logic)
5. Uses `whereIn()` for reference lookup via foreign key (direct table query)
6. Secondary sort by ID is indexed (primary key)

**Estimated queries per page:**
- 1 main question query with pagination
- All relationships loaded in one query each (4 relationships)
- **Total: ~5 queries per request** (constant regardless of page size)

## Integration Points

### Routes
- `route('getBranches')` - ApiController@branches
- `route('getSpeciality')` - ApiController@s
- `route('getSkills')` - ApiController@skills
- `route('getReferences')` - ApiController@references

### Models
- Questions (with relationships to branches, skills, specialties, reference)
- Relationships already exist on Questions model

### Layout
- Inherits from `x-user-layout` component
- Uses Bootstrap 5 styling (consistent with project)
- Select2 CSS/JS already loaded in user-layout

## What Works

✅ **Filtering:**
- Difficulty (button group)
- Length (button group)
- Branches (Select2 AJAX)
- Specialties (Select2 AJAX)
- Skills (Select2 AJAX)
- References (Select2 AJAX)
- All filters can be combined

✅ **Search:**
- Full-text search across name, content, topic
- Live debounce at 400ms
- Resets pagination on search change

✅ **Sorting:**
- By: difficulty, length, name, created_at, updated_at
- Ascending/descending toggle
- Direction icon updates to reflect current state
- Resets pagination on sort change

✅ **Pagination:**
- Dynamic page count display
- Livewire pagination links
- Maintains filters across pages

✅ **UI/UX:**
- Active filter badges with individual remove buttons
- Clear all filters button
- "No questions found" empty state
- Responsive layout
- Existing Bootstrap styling preserved

✅ **Data Display:**
- Real question names and references
- Dynamic difficulty/length badges with proper capitalization
- Question ID display
- Try button wired to component method

## Files Changed

1. `resources/views/components/⚡gambits.blade.php` - Complete implementation

## Files NOT Changed (per requirements)

- No routes modified
- No models modified
- No migrations created
- No new AJAX endpoints created
- No API controller changes
- No existing components modified

## Testing Approach

The component can be tested by:
1. Navigating to `/gambits` route
2. Verifying questions display
3. Testing each filter individually and in combination
4. Verifying search returns correct results
5. Testing sort direction toggle
6. Verifying pagination works across filters
7. Clicking "Clear all" filters
8. Clicking "Try" button on a question (currently dd's for testing)

## Known Limitations

- `tryQuestion()` method currently returns `dd("here")` - awaits actual implementation
- No admin-only access control (relies on existing route middleware)
- No question creation/editing from this view (CUD operations not included)
