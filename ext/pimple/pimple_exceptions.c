
/*
 * This file is part of Pimple.
 *
 * Copyright (c) 2014 Fabien Potencier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

#include "php.h"
#include "zend_interfaces.h"
#include "ext/spl/spl_exceptions.h"

#include "pimple_psr.h"
#include "pimple_exceptions.h"

PHPAPI zend_class_entry *pimple_ce_ExpectedInvokableException;
PHPAPI zend_class_entry *pimple_ce_FrozenServiceException;
PHPAPI zend_class_entry *pimple_ce_InvalidServiceIdentifierException;
PHPAPI zend_class_entry *pimple_ce_UnknownIdentifierException;

/* parent::__construct("Something with %s", $arg1) */
static void pimple_exception_call_parent_constructor(zval *this_ptr, const char *format, const char *arg1 TSRMLS_DC)
{
	zend_class_entry *ce = Z_OBJCE_P(this_ptr);
	char *message = NULL;
	int message_len;
	zval *constructor_arg;

	message_len = spprintf(&message, 0, format, arg1);
	ALLOC_INIT_ZVAL(constructor_arg);
	ZVAL_STRINGL(constructor_arg, message, message_len, 1);

	zend_call_method_with_1_params(&this_ptr, ce, &ce->parent->constructor, "__construct", NULL, constructor_arg);

	efree(message);
	zval_ptr_dtor(&constructor_arg);
}

ZEND_BEGIN_ARG_INFO_EX(arginfo_FrozenServiceException___construct, 0, 0, 1)
ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_InvalidServiceIdentifierException___construct, 0, 0, 1)
ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

ZEND_BEGIN_ARG_INFO_EX(arginfo_UnknownIdentifierException___construct, 0, 0, 1)
ZEND_ARG_INFO(0, id)
ZEND_END_ARG_INFO()

PHP_METHOD(FrozenServiceException, __construct)
{
	char *id = NULL;
	int id_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &id, &id_len) == FAILURE) {
		return;
	}
	pimple_exception_call_parent_constructor(getThis(), "Cannot override frozen service \"%s\".", id TSRMLS_CC);
}

PHP_METHOD(InvalidServiceIdentifierException, __construct)
{
	char *id = NULL;
	int id_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &id, &id_len) == FAILURE) {
		return;
	}
	pimple_exception_call_parent_constructor(getThis(), "Identifier \"%s\" does not contain an object definition.", id TSRMLS_CC);
}

PHP_METHOD(UnknownIdentifierException, __construct)
{
	char *id = NULL;
	int id_len;

	if (zend_parse_parameters(ZEND_NUM_ARGS() TSRMLS_CC, "s", &id, &id_len) == FAILURE) {
		return;
	}
	pimple_exception_call_parent_constructor(getThis(), "Identifier \"%s\" is not defined.", id TSRMLS_CC);
}

static const zend_function_entry pimple_ce_FrozenServiceException_functions[] = {
	PHP_ME(FrozenServiceException, __construct, arginfo_FrozenServiceException___construct, ZEND_ACC_PUBLIC)
	PHP_FE_END
};

static const zend_function_entry pimple_ce_InvalidServiceIdentifierException_functions[] = {
	PHP_ME(InvalidServiceIdentifierException, __construct, arginfo_InvalidServiceIdentifierException___construct, ZEND_ACC_PUBLIC)
	PHP_FE_END
};

static const zend_function_entry pimple_ce_UnknownIdentifierException_functions[] = {
	PHP_ME(UnknownIdentifierException, __construct, arginfo_UnknownIdentifierException___construct, ZEND_ACC_PUBLIC)
	PHP_FE_END
};

PHP_MINIT_FUNCTION(pimple_exceptions)
{
	zend_class_entry tmp_ce_ExpectedInvokableException;
	zend_class_entry tmp_ce_FrozenServiceException;
	zend_class_entry tmp_ce_InvalidServiceIdentifierException;
	zend_class_entry tmp_ce_UnknownIdentifierException;

	INIT_NS_CLASS_ENTRY(tmp_ce_ExpectedInvokableException,        PIMPLE_EXCEPTION_NS, "ExpectedInvokableException",         NULL);
	INIT_NS_CLASS_ENTRY(tmp_ce_FrozenServiceException,            PIMPLE_EXCEPTION_NS, "FrozenServiceException",             pimple_ce_FrozenServiceException_functions);
	INIT_NS_CLASS_ENTRY(tmp_ce_InvalidServiceIdentifierException, PIMPLE_EXCEPTION_NS, "InvalidServiceIdentifierException",  pimple_ce_InvalidServiceIdentifierException_functions);
	INIT_NS_CLASS_ENTRY(tmp_ce_UnknownIdentifierException,        PIMPLE_EXCEPTION_NS, "UnknownIdentifierException",         pimple_ce_UnknownIdentifierException_functions);

	pimple_ce_ExpectedInvokableException        = zend_register_internal_class_ex(&tmp_ce_ExpectedInvokableException, spl_ce_InvalidArgumentException, NULL TSRMLS_CC);
	pimple_ce_FrozenServiceException            = zend_register_internal_class_ex(&tmp_ce_FrozenServiceException, spl_ce_RuntimeException, NULL TSRMLS_CC);
	pimple_ce_InvalidServiceIdentifierException = zend_register_internal_class_ex(&tmp_ce_InvalidServiceIdentifierException, spl_ce_InvalidArgumentException, NULL TSRMLS_CC);
	pimple_ce_UnknownIdentifierException        = zend_register_internal_class_ex(&tmp_ce_UnknownIdentifierException, spl_ce_InvalidArgumentException, NULL TSRMLS_CC);

	zend_class_implements(pimple_ce_ExpectedInvokableException TSRMLS_CC,        1, pimple_ce_PsrContainerExceptionInterface);
	zend_class_implements(pimple_ce_FrozenServiceException TSRMLS_CC,            1, pimple_ce_PsrContainerExceptionInterface);
	zend_class_implements(pimple_ce_InvalidServiceIdentifierException TSRMLS_CC, 1, pimple_ce_PsrContainerExceptionInterface);
	zend_class_implements(pimple_ce_UnknownIdentifierException TSRMLS_CC,        1, pimple_ce_PsrNotFoundExceptionInterface);

	return SUCCESS;
}
