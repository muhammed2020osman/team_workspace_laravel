<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Helpers\DatabaseHelper;
class DynamicTableController extends BaseController
{
    /**
     * Check if the authenticated user has the specified role
     */
    public function hasRole($role)
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        // For Admin model - always has admin role
        if ($user instanceof \App\Models\Admin) {
            return $role === 'admin';
        }

        // For Driver model - always has driver role
        if ($user instanceof \App\Models\Driver) {
            return $role === 'driver';
        }

        // For User model - check the role field
        if ($user instanceof \App\Models\User && isset($user->role)) {
            return $user->role === $role;
        }

        return false;
    }

    /**
     * Check if user has any of the specified roles
     */
    public function hasAnyRole($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has all of the specified roles
     */
    public function hasAllRoles($roles)
    {
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check role-based permissions for table operations
     */
    public function checkTablePermission($operation, $table = null)
    {
        return true;
        // Admin can do everything
        if ($this->hasRole('admin')) {
            return true;
        }

        // Driver permissions
        if ($this->hasRole('driver')) {
            $allowedOperations = ['read', 'update'];
            return in_array($operation, $allowedOperations);
        }

        // Regular user permissions
        if ($this->hasRole('user')) {
            $allowedOperations = ['read'];
            return in_array($operation, $allowedOperations);
        }

        // Super admin can do everything
        $user = Auth::user();
        if ($user instanceof \App\Models\User && isset($user->role) && $user->role === 'super_admin') {
            return true;
        }

        return false;
    }

    /**
     * التحقق من وجود مودل للجدول وإنشاؤه أو تحديثه مع العلاقات
     */
    public function getOrCreateModelForTable(string $table, array $data = []): string
    {
        $modelName = Str::studly($table);
        $modelPath = app_path("Models/{$modelName}.php");

        if (!File::exists($modelPath)) {
            // إنشاء مودل جديد مع العلاقات المقترحة
            $this->createDynamicModel($table, $modelName, $data);
        } else {
            // تحديث المودل الموجود بالعلاقات الجديدة
            $this->updateModelWithNewRelationships($table, $modelName, $data);
        }

        return "App\\Models\\{$modelName}";
    }

    /**
     * تحديث المودل الموجود بالعلاقات الجديدة مع الحفاظ على السابقة
     */
    public function updateModelWithNewRelationships(string $table, string $modelName, array $data): void
    {
        $modelPath = app_path("Models/{$modelName}.php");
        $currentContent = File::get($modelPath);

        // تحليل العلاقات الجديدة
        $newRelationships = $this->analyzeFieldsForRelationships($data);

        // التحقق من وجود أعمدة timestamps وتحديث النموذج إذا لزم الأمر
        $this->ensureModelHasCorrectTimestamps($table, $modelName, $currentContent);

        if (empty($newRelationships)) {
            return; // لا توجد علاقات جديدة
        }

        // استخراج العلاقات الموجودة حالياً
        $existingMethods = $this->extractExistingRelationshipMethods($currentContent);

        // فلترة العلاقات الجديدة فقط
        $relationshipsToAdd = [];
        foreach ($newRelationships as $relation) {
            if (!in_array($relation['method'], $existingMethods)) {
                $relationshipsToAdd[] = $relation;
            }
        }

        if (empty($relationshipsToAdd)) {
            return; // جميع العلاقات موجودة مسبقاً
        }

        // إنشاء كود العلاقات الجديدة
        $newRelationshipMethods = $this->generateRelationshipMethods($relationshipsToAdd);

        // إضافة العلاقات الجديدة قبل نهاية الكلاس
        $updatedContent = str_replace(
            '    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة',
            $newRelationshipMethods . "\n    \n    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة",
            $currentContent
        );

        // إذا لم يتم العثور على النص المحدد، أضف قبل نهاية الكلاس مباشرة
        if ($updatedContent === $currentContent) {
            $updatedContent = str_replace(
                '}',
                $newRelationshipMethods . "\n}",
                $currentContent
            );
        }

        File::put($modelPath, $updatedContent);
    }

    /**
     * التحقق من وجود أعمدة timestamps في الجدول
     */
    public function tableHasTimestamps(string $table): bool
    {
        try {
            return Schema::hasColumns($table, ['created_at', 'updated_at']);
        } catch (\Exception $e) {
            // إذا فشل التحقق، نفترض عدم وجود timestamps
            return false;
        }
    }

    /**
     * التحقق من وجود عمود company_id في الجدول
     */
    public function tableHasCompanyId(string $table): bool
    {
        try {
            return Schema::hasColumn($table, 'company_id');
        } catch (\Exception $e) {
            // إذا فشل التحقق، نفترض عدم وجود company_id
            return false;
        }
    }

    /**
     * التأكد من أن النموذج يحتوي على إعدادات timestamps صحيحة
     */
    public function ensureModelHasCorrectTimestamps(string $table, string $modelName, string $currentContent): void
    {
        $modelPath = app_path("Models/{$modelName}.php");
        $hasTimestamps = $this->tableHasTimestamps($table);

        // التحقق من وجود public $timestamps = false;
        $hasTimestampsFalse = strpos($currentContent, 'public $timestamps = false;') !== false;

        // التحقق من وجود casts لـ created_at أو updated_at
        $hasTimestampCasts = preg_match('/\'created_at\'\s*=>\s*\'datetime\'/', $currentContent) ||
                             preg_match('/\'updated_at\'\s*=>\s*\'datetime\'/', $currentContent);

        $updatedContent = $currentContent;

        if (!$hasTimestamps) {
            // إذا لم يكن للجدول timestamps، تأكد من وجود public $timestamps = false;
            if (!$hasTimestampsFalse) {
                // إضافة public $timestamps = false; بعد protected $guarded
                $updatedContent = preg_replace(
                    '/(protected \$guarded = \[\'id\'\];)/',
                    '$1' . "\n    public \$timestamps = false;",
                    $updatedContent
                );
            }

            // إزالة casts لـ timestamps إذا كانت موجودة
            if ($hasTimestampCasts) {
                $updatedContent = preg_replace(
                    '/\s*\'created_at\'\s*=>\s*\'datetime\',?\s*/',
                    '',
                    $updatedContent
                );
                $updatedContent = preg_replace(
                    '/\s*\'updated_at\'\s*=>\s*\'datetime\',?\s*/',
                    '',
                    $updatedContent
                );

                // تنظيف casts array إذا أصبح فارغاً
                $updatedContent = preg_replace(
                    '/protected \$casts = \[\s*\];\s*/',
                    '',
                    $updatedContent
                );
            }
        } else {
            // إذا كان للجدول timestamps، تأكد من عدم وجود public $timestamps = false;
            if ($hasTimestampsFalse) {
                $updatedContent = str_replace('public $timestamps = false;', '', $updatedContent);
            }
        }

        // حفظ التغييرات إذا كان هناك تحديث
        if ($updatedContent !== $currentContent) {
            File::put($modelPath, $updatedContent);
        }
    }

    /**
     * استخراج أسماء طرق العلاقات الموجودة في المودل
     */
    public function extractExistingRelationshipMethods(string $content): array
    {
        $methods = [];
        preg_match_all('/public function (\w+)\(\)/', $content, $matches);

        if (isset($matches[1])) {
            $methods = $matches[1];
        }

        return $methods;
    }

    /**
     * التأكد من وجود مودل العلاقة وإنشاؤه إذا لم يكن موجوداً
     */
    public function ensureRelatedModelExists(string $relatedTable, string $relatedModel): void
    {
        $relatedModelPath = app_path("Models/{$relatedModel}.php");

        if (!File::exists($relatedModelPath)) {
            // إنشاء مودل العلاقة الأساسي
            $this->createBasicRelatedModel($relatedTable, $relatedModel);
        }
    }

    /**
     * إنشاء مودل أساسي للعلاقة
     */
    public function createBasicRelatedModel(string $table, string $modelName): void
    {
        $modelContent = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    protected \$table = '{$table}';
    protected \$guarded = ['id'];

    // مودل تم إنشاؤه تلقائياً للعلاقات
    // يمكن إضافة حقول وعلاقات حسب الحاجة
}";

        File::put(app_path("Models/{$modelName}.php"), $modelContent);
    }

    /**
     * الحصول على جميع العلاقات المتاحة في المودل مع البيانات
     */
    public function getAllModelRelationships(string $modelClass, array $custom_relations = []): array
    {
        $relationships = [];

        try {
            $model = new $modelClass();
            $reflection = new \ReflectionClass($model);

            // البحث عن جميع الطرق العامة في المودل
            foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
                // تجاهل الطرق المحجوزة والطرق التي تحتاج معاملات
                if (
                    $method->getNumberOfParameters() == 0 &&
                    !in_array($method->getName(), [
                        '__construct',
                        '__get',
                        '__set',
                        '__isset',
                        '__unset',
                        '__call',
                        '__callStatic',
                        'getTable',
                        'getKeyName',
                        'getKey',
                        'getRouteKeyName',
                        'resolveRouteBinding',
                        'getIncrementing',
                        'getKeyType',
                        'getConnectionName',
                        'getFillable',
                        'getGuarded',
                        'getHidden',
                        'getVisible',
                        'getCasts',
                        'getDates',
                        'getDateFormat',
                        'getCreatedAtColumn',
                        'getUpdatedAtColumn',
                        'getDeletedAtColumn',
                        'getQualifiedKeyName',
                        'getQualifiedCreatedAtColumn',
                        'getQualifiedUpdatedAtColumn',
                        'getQualifiedDeletedAtColumn',
                        'getMorphClass',
                        'toArray',
                        'toJson',
                        'jsonSerialize',
                        'fresh',
                        'refresh',
                        'replicate',
                        'is',
                        'isNot',
                        'getConnection',
                        'newQuery',
                        'newQueryWithoutScopes',
                        'newQueryWithoutScope',
                        'newQueryForRestoration',
                        'newEloquentBuilder',
                        'newBaseQueryBuilder',
                        'newCollection',
                        'newPivot',
                        'qualifyColumn',
                        'removeTableFromKey',
                        'getRelationValue',
                        'isRelation',
                        'relationLoaded',
                        'setRelation',
                        'unsetRelation',
                        'getRelations',
                        'setRelations',
                        'withoutRelations',
                        'getTouchedRelations',
                        'setTouchedRelations',
                        'touches',
                        'touchOwners',
                        'newBelongsTo',
                        'newMorphTo',
                        'newHasOne',
                        'newHasOneThrough',
                        'newMorphOne',
                        'newHasMany',
                        'newHasManyThrough',
                        'newMorphMany',
                        'newBelongsToMany',
                        'newMorphToMany',
                        'newMorphedByMany',
                        'getAttribute',
                        'getAttributeValue',
                        'getRelationshipFromMethod',
                        'hasGetMutator',
                        'hasSetMutator',
                        'hasCast',
                        'isDateCastable',
                        'isJsonCastable',
                        'isEncryptedCastable',
                        'isClassCastable',
                        'resolveCasterClass',
                        'getCastType',
                        'setAttribute',
                        'hasSetMutator',
                        'setMutatedAttributeValue',
                        'isDateAttribute',
                        'fillJsonAttribute',
                        'getArrayAttributeWithValue',
                        'getArrayAttributeByKey',
                        'castAttribute',
                        'getCastValue',
                        'setAttribute',
                        'getAttributes',
                        'setRawAttributes',
                        'getOriginal',
                        'only',
                        'syncOriginal',
                        'syncOriginalAttribute',
                        'syncOriginalAttributes',
                        'syncChanges',
                        'isDirty',
                        'isClean',
                        'wasChanged',
                        'hasChanges',
                        'getDirty',
                        'getChanges',
                        'originalIsEquivalent',
                        'append',
                        'hasAppended',
                        'getMutatedAttributes',
                        'cacheMutatedAttributes'
                    ]) &&
                    !str_starts_with($method->getName(), 'get') &&
                    !str_starts_with($method->getName(), 'set') &&
                    !str_starts_with($method->getName(), 'scope') &&
                    $method->getDeclaringClass()->getName() === $modelClass
                ) {
                    // محاولة تشغيل الطريقة للتحقق من كونها علاقة
                    try {
                        $result = $model->{$method->getName()}();

                        // التحقق من كون النتيجة علاقة Eloquent
                        if ($result instanceof \Illuminate\Database\Eloquent\Relations\Relation) {
                            $relationships[] = $method->getName();
                        }
                    } catch (\Exception $e) {
                        // تجاهل الأخطاء - قد تكون الطريقة تحتاج معاملات أو ليست علاقة
                    }
                }
            }
        } catch (\Exception $e) {
            // تجاهل أخطاء إنشاء المودل
        }

        // دمج العلاقات المخصصة مع العلاقات المكتشفة تلقائياً
        if (!empty($custom_relations)) {
            $relationships = array_unique(array_merge($relationships, $custom_relations));
        }

        return $relationships;
    }

    /**
     * الحصول على البيانات مع تحميل العلاقات وبياناتها
     */
    /**
     * Get data with relationships and apply dynamic filters
     *
     * @param string $modelClass The model class to query
     * @param array $relationships Relationships to eager load
     * @param mixed $company_id Company ID filter
     * @param string|null $filterby Field to filter by (legacy parameter)
     * @param mixed $filter_value Value to filter by (legacy parameter)
     * @param array $custom_relations Additional custom relationships to load
     * @param array $requestFilters Additional filters from request query parameters
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function getDataWithRelationships(
        string $modelClass,
        array $relationships,
        $company_id = null,
        $filterby = null,
        $filter_value = null,
        array $custom_relations = [],
        array $requestFilters = []
    ) {
        // \Log::info('Starting getDataWithRelationships', [
        //     'modelClass' => $modelClass,
        //     'requestFilters' => $requestFilters,
        //     'all_request_params' => request()->all()
        // ]);

        $query = $modelClass::query();

        // Merge custom relationships with existing ones
        $allRelationships = $relationships;
        if (!empty($custom_relations)) {
            $allRelationships = array_unique(array_merge($relationships, $custom_relations));
        }

        // Eager load relationships
        if (!empty($allRelationships)) {
            $query->with($allRelationships);
        }

        // Apply company filter based on authenticated user
        $user = Auth::user();
        if ($user) {
            if (isset($user->role) && $user->role != 'super_admin' && isset($user->company_id)) {
                $query->where('company_id', $user->company_id);
            } elseif ($company_id != null) {
                $query->where('company_id', $company_id);
            }
        }

        // Apply legacy filter if provided (for backward compatibility)
        if ($filterby && $filter_value) {
            $query->where($filterby, $filter_value);
        }

        // Apply dynamic filters from request query parameters
        $modelInstance = new $modelClass;
        $tableName = $modelInstance->getTable();
        $fillable = $modelInstance->getFillable();

        \Log::info('Model info', [
            'table_name' => $tableName,
            'fillable_fields' => $fillable,
            'model_class' => get_class($modelInstance)
        ]);

        // Special case for expense categories
        if ($tableName == 'expensecategories') {
            $query->where('parent_id', 0)->orWhereNull('parent_id');
        }

        // Apply filters using the model's scope when available; otherwise apply a basic fallback
        if (method_exists($modelInstance, 'scopeApplyFilters')) {
            $query->applyFilters($requestFilters);
        } else {
            if (!empty($requestFilters)) {
                $schema = $modelInstance->getConnection()->getSchemaBuilder();
                $columns = $schema->getColumnListing($tableName);

                foreach ($requestFilters as $field => $value) {
                    if (in_array($field, ['page', 'per_page', 'sort_by', 'sort_order']) || $value === '' || $value === null) {
                        continue;
                    }

                    // Global search across string-like columns
                    if ($field === 'search' && is_string($value)) {
                        $query->where(function ($q) use ($schema, $tableName, $value) {
                            $first = true;
                            foreach ($schema->getColumnListing($tableName) as $column) {
                                $columnType = $schema->getColumnType($tableName, $column);
                                if (!in_array($columnType, ['string', 'text', 'varchar', 'char'])) {
                                    continue;
                                }
                                $method = $first ? 'where' : 'orWhere';
                                $q->$method($column, 'LIKE', "%{$value}%");
                                $first = false;
                            }
                        });
                        continue;
                    }

                    // Field-specific filter only if column exists (or common id)
                    if (in_array($field, $columns) || $field === 'id') {
                        if ($field === 'driver_id' && $value === 'unassigned') {
                            $query->whereNull('driver_id')->orWhere('driver_id', '');
                        } else {
                            $query->where($field, $value);
                        }
                    }
                }
            }
        }

        // Get pagination parameters
        $perPage = request()->input('per_page', 10);
        $page = request()->input('page', 1);

        \Log::info('Executing final query', [
            'per_page' => $perPage,
            'page' => $page,
            'sql' => $query->toSql(),
            'bindings' => $query->getBindings()
        ]);

        $result = $query->paginate($perPage, ['*'], 'page', $page);

        \Log::info('Query executed successfully', [
            'total' => $result->total(),
            'per_page' => $result->perPage(),
            'current_page' => $result->currentPage(),
            'last_page' => $result->lastPage()
        ]);

        return $result;
    }

    /**
     * تحليل الحقول واقتراح العلاقات مع التأكد من وجود مودلات العلاقات
     */
    public function analyzeFieldsForRelationships(array $data): array
    {
        $relationships = [];
        $addedMethods = []; // لتجنب التكرار

        foreach ($data as $field => $value) {
            // البحث عن حقول تنتهي بـ _id (Foreign Keys)
            if (str_ends_with($field, '_id')) {
                $relatedTable = str_replace('_id', '', $field);
                $relatedModel = Str::studly($relatedTable);
                $methodName = $relatedTable;

                // تجنب إضافة نفس العلاقة مرتين
                if (!in_array($methodName, $addedMethods)) {
                    // التأكد من وجود مودل العلاقة
                    $this->ensureRelatedModelExists($relatedTable, $relatedModel);

                    $relationships[] = [
                        'type' => 'belongsTo',
                        'method' => $methodName,
                        'model' => $relatedModel,
                        'foreign_key' => $field
                    ];
                    $addedMethods[] = $methodName;
                }
            }
        }

        return $relationships;
    }

    /**
     * إنشاء كود العلاقات للمودل
     */
    public function generateRelationshipMethods(array $relationships): string
    {
        $methods = "";

        foreach ($relationships as $relation) {
            $methods .= "
    /**
     * علاقة {$relation['type']} مع {$relation['model']}
     */
    public function {$relation['method']}()
    {
        return \$this->{$relation['type']}({$relation['model']}::class, '{$relation['foreign_key']}');
    }";
        }

        return $methods;
    }

    /**
     * تحديد الحقول التي تحتاج إلى JSON casting
     */
    public function getJsonCastFields(array $data): array
    {
        $jsonFields = [];

        foreach ($data as $field => $value) {
            if (is_array($value) || is_object($value)) {
                $jsonFields[] = "'{$field}' => 'array'";
            }
        }

        return $jsonFields;
    }

    /**
     * إنشاء مودل ديناميكي للجدول مع العلاقات المقترحة و JSON casting
     */
    public function createDynamicModel(string $table, string $modelName, array $data = []): void
    {
        // تحليل الحقول لاقتراح العلاقات
        $relationships = $this->analyzeFieldsForRelationships($data);
        $relationshipMethods = $this->generateRelationshipMethods($relationships);

        // تحديد الحقول التي تحتاج JSON casting
        $jsonCastFields = $this->getJsonCastFields($data);
        $castsArray = !empty($jsonCastFields) ? "\n    protected \$casts = [\n        " . implode(",\n        ", $jsonCastFields) . "\n    ];" : '';

        // التحقق من وجود أعمدة timestamps
        $hasTimestamps = $this->tableHasTimestamps($table);
        $timestampsConfig = $hasTimestamps ? '' : "\n    public \$timestamps = false;";

        $modelContent = "<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class {$modelName} extends Model
{
    protected \$table = '{$table}';
    protected \$guarded = ['id'];{$timestampsConfig}{$castsArray}

    // العلاقات المقترحة تلقائياً{$relationshipMethods}

    // يمكن إضافة علاقات أو طرق خاصة أخرى هنا حسب الحاجة
}";

        File::put(app_path("Models/{$modelName}.php"), $modelContent);
    }



    /**
     * 🟢 Create - إضافة صف جديد
     */
    public function store(Request $request): JsonResponse
    {
        // Check role-based permissions
        if (!$this->checkTablePermission('create')) {
            return $this->error([], 'ليس لديك صلاحية لإنشاء بيانات جديدة', 403);
        }

        // $request->validate([
        //     'newData'  => 'required',
        // ]);

        // استخراج اسم الجدول من المسار
        $table = $this->extractTableNameFromPath($request);
        $data  = $request->all();

        DatabaseHelper::ensureTableAndColumns($table, $data);

        // معالجة البيانات قبل الحفظ
        $processedData = DatabaseHelper::processDataForStorage($data);

        // إنشاء أو الحصول على المودل للجدول مع العلاقات المقترحة حسب الحقول
        $modelClass = $this->getOrCreateModelForTable($table, $data);
                // التحقق من وجود company_id في الجدول وإضافته تلقائياً
        $company_id = $this->getCompanyId($request);
        if ($company_id !== null && $this->tableHasCompanyId($table)) {
            $processedData['company_id'] = $company_id;
        }

        // إنشاء السجل باستخدام المودل
        $record = $modelClass::create($processedData);
        $id = $record->id;
        $this->syncRelationships($request,  $record);
        return $this->createdResponse(
            $data,
            "تمت إضافة سجل جديد في جدول '$table'"
        );
    }
    public function syncRelationships(Request $request, $modelClass)
    {
        // تحقق أولاً أن $relationFields معرف
        if (property_exists($modelClass, 'relationFields')) {
            foreach ($modelClass::$relationFields as $field => $relation) {
                if ($request->has($field)) {
                    if ($relation == 'commissions') {
                        $modelClass->$relation()->createMany($request->input($field)['tiers']);
                    } else {
                        $modelClass->$relation()->sync($request->input($field));
                    }
                }
            }
        }
    }
    /**
     * استخراج اسم الجدول من مسار الطلب
     */
    public function extractTableNameFromPath(Request $request): string
    {
        $path = $request->path();

        // إضافة debug log
        \Log::info('Request path: ' . $path);

        // تقسيم المسار إلى أجزاء
        $pathParts = explode('/', trim($path, '/'));
        \Log::info('Path parts: ' . json_encode($pathParts));

        // البحث عن كلمة "tables" في المسار
        $tablesIndex = array_search('tables', $pathParts);

        if ($tablesIndex !== false && isset($pathParts[$tablesIndex + 1])) {
            // الكلمة بعد "tables" هي اسم الجدول
            $tableName = $pathParts[$tablesIndex + 1];
            \Log::info('Found table name after "tables": ' . $tableName);
            return $tableName;
        }

        // إذا لم يتم العثور على "tables"، استخدم آخر جزء من المسار
        $tableName = end($pathParts);
        \Log::info('Using last path part as table name: ' . $tableName);

        return $tableName;
    }

    /**
     * Get company ID from authenticated user
     */
    public function getCompanyId(Request $request)
    {
        $user = $request->user();
        $company_id = null;

        if ($user && isset($user->company_id) && $user->company_id != 0) {
            $company_id = $user->company_id;
            if ($user->role == 'super_admin')
                $company_id = null;
        }

        return $company_id;
    }

    /**
     * 🔵 Read - قراءة كل الصفوف مع جميع العلاقات
     */
    public function index(Request $request): JsonResponse
    {
        // Check role-based permissions
        if (!$this->checkTablePermission('read')) {
            return $this->error([], 'ليس لديك صلاحية لقراءة البيانات', 403);
        }

        // استخراج اسم الجدول من المسار
        $table = $this->extractTableNameFromPath($request);
        \Log::info('Table name extracted: ' . $table);

        $company_id = $this->getCompanyId($request);

        // إنشاء الجدول إذا لم يكن موجوداً
        if (!Schema::hasTable($table)) {
            // إنشاء جدول أساسي بحقول افتراضية
            DatabaseHelper::ensureTableAndColumns($table, []);
        }

        // استخدام المودل للحصول على البيانات مع جميع العلاقات
        $modelClass = $this->getOrCreateModelForTable($table);
        $custom_relations = $request->get('custom_relations', []);
        $relationships = $this->getAllModelRelationships($modelClass, $custom_relations);
        $filterby = $request->get('filterby');
        $filter_value = $request->get('filter_value');

        // Get all query parameters for dynamic filtering
        $requestFilters = $request->except(['custom_relations', 'filterby', 'filter_value']);

        // جلب البيانات مع تحميل العلاقات وبياناتها
        $rows = $this->getDataWithRelationships(
            $modelClass,
            $relationships,
            $company_id,
            $filterby,
            $filter_value,
            $custom_relations,
            $requestFilters
        );

        return $this->success(
            $rows
            //     'company_id' => $company_id,
            //     'user' => $request->user(),
            //     'model' => $modelClass,
            //     'relationships_loaded' => $relationships,
            //     'table_created' => !Schema::hasTable($table)
            // ]
            ,
            'تم جلب البيانات بنجاح'
        );
    }

    /**
     * 🔍 Show - عرض صف واحد حسب ID مع جميع العلاقات
     */
    public function showRecord(Request $request, $table, $id): JsonResponse
    {
        // Check role-based permissions
        if (!$this->checkTablePermission('read')) {
            return $this->error([], 'ليس لديك صلاحية لقراءة البيانات', 403);
        }

        // استخراج اسم الجدول من المسار
        $table = $this->extractTableNameFromPath($request);
        \Log::info('Show method - Table name: ' . $table . ', ID parameter: ' . $id);

        // إذا كان ID هو اسم الجدول، فهذا يعني أن Laravel لم يطابق المسار الصحيح
        if ($id === $table) {
            \Log::error('Route matching issue: ID parameter is same as table name. This suggests wrong route was matched.');
            return $this->error([
                'table' => $table,
                'id_parameter' => $id,
                'request_path' => $request->path(),
                'route_name' => $request->route()->getName() ?? 'unknown'
            ], "خطأ في تطابق المسار - تم تمرير اسم الجدول كـ ID", 400);
        }

        // إنشاء الجدول إذا لم يكن موجوداً
        $tableCreated = false;
        if (!Schema::hasTable($table)) {
            // إنشاء جدول أساسي بحقول افتراضية
            DatabaseHelper::ensureTableAndColumns($table, []);
            $tableCreated = true;
        }

        // استخدام المودل للبحث عن السجل مع جميع العلاقات
        $modelClass = $this->getOrCreateModelForTable($table);
        $custom_relations = $request->get('custom_relations', []);
        $relationships = $this->getAllModelRelationships($modelClass, $custom_relations);

        // دمج العلاقات المخصصة مع العلاقات الموجودة
        $allRelationships = $relationships;
        if (!empty($custom_relations)) {
            $allRelationships = array_unique(array_merge($relationships, $custom_relations));
        }

        // جلب السجل مع تحميل جميع العلاقات
        if (!empty($allRelationships)) {
            $record = $modelClass::with($allRelationships)->find($id);
        } else {
            $record = $modelClass::find($id);
        }

        if (!$record) {
            return $this->notFoundResponse("السجل غير موجود");
        }

        return $this->success(
            // 'payload' =>
            $record
            // 'relationships_loaded' => $relationships,
            // 'table_created' => $tableCreated
            ,
            'تم جلب السجل بنجاح'
        );
    }

    /**
     * 🟡 Update - تعديل صف حسب ID
     */
    public function update(Request $request, $table, $id): JsonResponse
    {
        // Check role-based permissions
        if (!$this->checkTablePermission('update')) {
            return $this->error([], 'ليس لديك صلاحية لتعديل البيانات', 403);
        }

        // $request->validate([
        //     'data'  => 'required|array',
        // ]);

        // استخراج اسم الجدول من المسار
        $table = $this->extractTableNameFromPath($request);
        $data  = $request->all();

        DatabaseHelper::ensureTableAndColumns($table, $data);

        // معالجة البيانات قبل التحديث
        $processedData = DatabaseHelper::processDataForStorage($data);

        // التحقق من وجود company_id في الجدول وإضافته تلقائياً للتحديث
        $company_id = $this->getCompanyId($request);
        if ($company_id !== null && $this->tableHasCompanyId($table)) {
            $processedData['company_id'] = $company_id;
        }

        // استخدام المودل للتحديث
        $modelClass = $this->getOrCreateModelForTable($table);
        $record = $modelClass::find($id);
        $updated = $record ? $record->update($processedData) : false;

        if ($updated > 0) {
            return $this->updatedResponse($record, "تم تحديث السجل رقم $id في جدول '$table'");
        } else {
            return $this->notFoundResponse("لم يتم العثور على السجل");
        }
    }

    /**
     * 🔴 Delete - حذف صف حسب ID
     */
    public function destroyRecord(Request $request, $t, $id): JsonResponse
    {
        // Check role-based permissions
        if (!$this->checkTablePermission('delete')) {
            return $this->error([], 'ليس لديك صلاحية لحذف البيانات', 403);
        }

        // استخراج اسم الجدول من المسار
        $table = $this->extractTableNameFromPath($request);

        if (!Schema::hasTable($table)) {
            return $this->notFoundResponse("الجدول '$table' غير موجود");
        }

        // استخدام المودل للحذف
        $modelClass = $this->getOrCreateModelForTable($table);
        $record = $modelClass::find($id);
        $deleted = $record ? $record->delete() : false;

        if ($deleted > 0) {
            return $this->deletedResponse("تم حذف السجل رقم $id من جدول '$table'");
        } else {
            return $this->notFoundResponse("لم يتم العثور على السجل");
        }
    }
    public function list(Request $request, $table): JsonResponse
    {
        $modelClass = $this->getOrCreateModelForTable($table);
        $data = $modelClass::select('name', 'id')->get();
        return $this->success($data, 'تم جلب البيانات بنجاح');
    }
}
