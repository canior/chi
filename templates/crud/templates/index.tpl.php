<?= $helper->getHeadPrintCode($entity_class_name) ?>

{% import 'form/macros.html.twig' as macros %}

{% block body %}
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    {% include "backend/includes/app.session.flashbag.html.twig" %}
                    <h3 class="box-title">总计 ({{ pagination.getTotalItemCount }})</h3>
                    <a href="{{ path('<?= $route_name ?>_new') }}" type="button" class="btn btn-info pull-right">添加</a>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div class="row">
                        <form class="cmxform" id="myForm">
                            <div class="col-md-3 form-group">
                                <label>关键词</label>
                                <input type="text" name="keyword" class="form-control" value="{{ form.keyword }}"
                                       placeholder="按 name 查询">
                            </div>
                            <div class="col-md-3 form-group">
                                <label>&nbsp;</label>
                                <input class="form-control btn btn-primary" type="submit" value="搜索"/>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                            <?php foreach ($entity_fields as $field): ?>
                                <th width="10%"><?= ucfirst($field['fieldName']) ?></th>
                            <?php endforeach; ?>
                                <th width="10%" class="text-center hidden-xs">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            {% if pagination.getTotalItemCount %}
                            {% for item in pagination %}
                            <tr>
                            <?php foreach ($entity_fields as $field): ?>
                                <td>{{ item.<?= $field['fieldName'] ?> }}</td>
                            <?php endforeach; ?>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="{{ path('<?= $route_name ?>_edit', {'id': item.id}) }}" title="修改">
                                        <span class="fa fa-edit"></span>
                                        </a>&nbsp;
                                        <a class="delete" data-toggle="modal" data-target="#modal-delete"
                                           data-url="{{ path('<?= $route_name ?>_delete', {'id': item.id}) }}"
                                           data-item='{"name": "{{ macros.cut(item.name, 30) }}"}'
                                           data-token="{{ csrf_token('delete' ~ item.id) }}"
                                           title="删除">
                                            <span class="fa fa-trash"></span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            {% endfor %}
                            {% else %}
                            <tr>
                                <td colspan="<?= (count($entity_fields) + 1) ?>" class="alert">no records found</td>
                            </tr>
                            {% endif %}
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer clearfix">
                    <div class="no-margin pull-right">
                        {{ knp_pagination_render(pagination) }}
                    </div>
                </div>
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
    <!-- /.row -->

    {% include 'backend/includes/delete.modal.html.twig' with {title: '<?= $entity_class_name ?>'} %}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    {% include '/backend/includes/delete.js.html.twig' with {title: '<?= $entity_class_name ?>'} %}
{% endblock %}
