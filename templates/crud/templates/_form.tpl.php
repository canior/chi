{{ form_start(form) }}
    {{ form_widget(form) }}
    <a class="btn btn-danger" href="{{ path('<?= $route_name ?>_index') }}">{{ 'Back to list'|trans }}</a>
    <button class="btn btn-primary">{{ button_label|default('Save') }}</button>
{{ form_end(form) }}