<?php
return (new PhpCsFixer\Config())->setRiskyAllowed(false)->setRules([
 '@PSR12'=>true,
 'no_multiple_statements_per_line'=>true,
 'control_structure_braces'=>true,
 'control_structure_continuation_position'=>true,
 'statement_indentation'=>true,
 'array_indentation'=>true,
 'array_syntax'=>['syntax'=>'short'],
 'binary_operator_spaces'=>['default'=>'single_space'],
 'whitespace_after_comma_in_array'=>true,
 'concat_space'=>['spacing'=>'none'],
 'trailing_comma_in_multiline'=>false,
 'single_quote'=>false,
 'blank_line_before_statement'=>false,
])->setIndent('    ')->setLineEnding("\n");
