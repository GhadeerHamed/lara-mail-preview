<?php

namespace Ghadeer\LaraMailPreview\Http\Controllers;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\View;
use ReflectionClass;
use ReflectionException;
use ReflectionNamedType;
use ReflectionParameter;
use Illuminate\Support\Facades\Schema;
use UnitEnum;

class MailPreviewController extends Controller
{
    public function showForm()
    {
        $mailableFiles = File::allFiles(app_path('Mail'));

        $mailableClasses = [];

        foreach ($mailableFiles as $mailable) {
            $fileName = $mailable->getFilename();

            $path = str($mailable->getPath())->replace('/', '\\')->afterLast('app\\');
            $class = "App\\" . $path . "\\" . str($fileName)->before('.php');

            $baseName = class_basename($class);
            if (class_exists($class)) {
                $mailableClasses[] = ['name' => $baseName, 'class' => $class];
            }
        }

        return View::make('email-preview::form', ['mailables' => $mailableClasses]);
    }

    /**
     * @throws ReflectionException
     */
    public function handleForm(Request $request)
    {
        $mailableClass = $request->get('mailClass');
        if (class_exists($mailableClass)) {
            $reflectionClass = new ReflectionClass($mailableClass);

            $constructor = $reflectionClass->getConstructor();
            $parameters = collect($constructor->getParameters())?->sortBy(fn(ReflectionParameter $p) => $p->getPosition());

            $parametersArray = [];
            $parameters->each(function (ReflectionParameter $p) use (&$parametersArray) {
                $paramType = $p->getType();
                if ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltin()) {
                    $options = $this->getModelParamType($paramType->getName());

                    $parameter['type'] = ['name' => $paramType->getName(), 'options' => $options];
                } else {
                    $parameter['type'] = ['name' => $paramType?->getName(), 'options' => null];
                }

                $parameter['name'] = $p->getName();
                $parameter['allow_null'] = $p->isOptional();

                if ($p->isOptional())
                    $parameter['default'] = $p->getDefaultValue();

                $parametersArray[$p->getName()] = $parameter;
            });

            return ['data' => $parametersArray];
        }

        abort(404, "Class '$mailableClass' not found");
    }

    /**
     * @throws ReflectionException
     */
    public function render(Request $request)
    {
        $mailableClass = $request->get('mailClass');
        $reflectionClass = new ReflectionClass($mailableClass);
        $constructor = $reflectionClass->getConstructor();
        $parameters = collect($constructor->getParameters())?->sortBy(fn(ReflectionParameter $p) => $p->getPosition());

        $parametersArray = [];
        $parameters->each(function (ReflectionParameter $param) use ($request, &$parametersArray) {
            $value = $request->get($param->getName());
            if (!$param->isOptional() && $value === null && !$param->isDefaultValueAvailable()) {
                abort(400, "Required parameter '" . $param->getName() . "' not found");
            }

            $paramType = $param->getType();
            if ($paramType instanceof ReflectionNamedType && !$paramType->isBuiltin()) {
                $parametersArray[] = $this->getModelParamValue($paramType->getName(), $value);
            } else {
                $parametersArray[] = $value;
            }
        });

        /** @var Mailable $mailableObject */
        $mailableObject = new $mailableClass(...$parametersArray);
        $render = $mailableObject->render();

        return response()->json(['data' => $render]);
    }

    /**
     * @throws ReflectionException
     */
    private function getModelParamType(string $className): array
    {
        $paramClass = new ReflectionClass($className);

        if ($paramClass->isSubclassOf(Model::class)) {
            /** @var Model $class */
            $class = new $className();

            $column = match (true) {
                Schema::hasColumn($class->getTable(), 'name') => 'name',
                Schema::hasColumn($class->getTable(), 'title') => 'title',
                Schema::hasColumn($class->getTable(), 'description') => 'description',
                Schema::hasColumn($class->getTable(), 'author') => 'author',
                true => $class->getKeyName(),
            };

            return $class->query()->pluck($column, $class->getKeyName())->toArray();
        } elseif ($paramClass->isSubclassOf(UnitEnum::class)) { // Enum in PHP 8.1+

            $values = collect($paramClass->getConstants())
                ->transform(fn(UnitEnum $constant) => $constant->value)
                ->toArray();
            $data = [];
            foreach ($values as $value) {
                $data[$value] = $value;
            }
            return $data;
        }
        return [];
    }

    /**
     * @throws ReflectionException
     */
    private function getModelParamValue(string $className, $value)
    {
        $paramClass = new ReflectionClass($className);

        if ($paramClass->isSubclassOf(Model::class)) {
            /** @var Model $class */
            $class = new $className();

            return $class->find($value);
        } elseif ($paramClass->isSubclassOf(UnitEnum::class)) { // Enum in PHP 8.1+

            return collect($paramClass->getConstants())
                ->filter(fn(UnitEnum $constant) => $constant->value === $value)
                ->first();
        }

        return null;
    }
}
