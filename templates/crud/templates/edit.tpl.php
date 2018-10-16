<?= $helper->getHeadPrintCode($entity_class_name) ?>

{% block body %}
    <div class="row">
        <div class="col-xs-12">
            <!-- general form elements disabled -->
            <div class="box">
                <div class="box-header with-border">
                    {% include "backend/includes/app.session.flashbag.html.twig" %}
                    <h3 class="box-title">Edit <?= $entity_class_name ?></h3>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    {{ include('backend/<?= $route_name ?>/_form.html.twig', {'button_label': 'Update'}) }}
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!--/.col (right) -->
    </div>
    <!-- /.row -->
{% endblock %}