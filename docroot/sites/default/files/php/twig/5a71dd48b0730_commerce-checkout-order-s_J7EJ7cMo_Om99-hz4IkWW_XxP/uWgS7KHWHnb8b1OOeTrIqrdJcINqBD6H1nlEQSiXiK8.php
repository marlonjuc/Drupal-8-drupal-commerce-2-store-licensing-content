<?php

/* modules/commerce/modules/checkout/templates/commerce-checkout-order-summary.html.twig */
class __TwigTemplate_b9470a2971c3d950c946cfd500159ead925cf564754445e3ba614eb9a62a8829 extends Twig_Template
{
    public function __construct(Twig_Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = array(
            'order_items' => array($this, 'block_order_items'),
            'totals' => array($this, 'block_totals'),
        );
    }

    protected function doDisplay(array $context, array $blocks = array())
    {
        $tags = array("block" => 24, "for" => 27, "if" => 30);
        $filters = array("number_format" => 29, "commerce_entity_render" => 31, "commerce_price_format" => 35);
        $functions = array();

        try {
            $this->env->getExtension('Twig_Extension_Sandbox')->checkSecurity(
                array('block', 'for', 'if'),
                array('number_format', 'commerce_entity_render', 'commerce_price_format'),
                array()
            );
        } catch (Twig_Sandbox_SecurityError $e) {
            $e->setSourceContext($this->getSourceContext());

            if ($e instanceof Twig_Sandbox_SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof Twig_Sandbox_SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

        // line 23
        echo "<div";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute((isset($context["attributes"]) ? $context["attributes"] : null), "addClass", array(0 => "checkout-order-summary"), "method"), "html", null, true));
        echo ">
  ";
        // line 24
        $this->displayBlock('order_items', $context, $blocks);
        // line 41
        echo "  ";
        $this->displayBlock('totals', $context, $blocks);
        // line 44
        echo "</div>";
    }

    // line 24
    public function block_order_items($context, array $blocks = array())
    {
        // line 25
        echo "    <table>
      <tbody>
      ";
        // line 27
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable($this->getAttribute((isset($context["order_entity"]) ? $context["order_entity"] : null), "getItems", array()));
        foreach ($context['_seq'] as $context["_key"] => $context["order_item"]) {
            // line 28
            echo "        <tr>
          <td>";
            // line 29
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, twig_number_format_filter($this->env, $this->getAttribute($context["order_item"], "getQuantity", array())), "html", null, true));
            echo "&nbsp;x</td>
          ";
            // line 30
            if ($this->getAttribute($context["order_item"], "hasPurchasedEntity", array())) {
                // line 31
                echo "            <td>";
                echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\commerce\TwigExtension\CommerceTwigExtension')->renderEntity($this->getAttribute($context["order_item"], "getPurchasedEntity", array()), "summary"), "html", null, true));
                echo "</td>
          ";
            } else {
                // line 33
                echo "            <td>";
                echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->getAttribute($context["order_item"], "label", array()), "html", null, true));
                echo "</td>
          ";
            }
            // line 35
            echo "          <td>";
            echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, $this->env->getExtension('Drupal\commerce_price\TwigExtension\PriceTwigExtension')->formatPrice($this->getAttribute($context["order_item"], "getTotalPrice", array())), "html", null, true));
            echo "</td>
        </tr>
      ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['order_item'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 38
        echo "      </tbody>
    </table>
  ";
    }

    // line 41
    public function block_totals($context, array $blocks = array())
    {
        // line 42
        echo "    ";
        echo $this->env->getExtension('Twig_Extension_Sandbox')->ensureToStringAllowed($this->env->getExtension('Drupal\Core\Template\TwigExtension')->escapeFilter($this->env, (isset($context["rendered_totals"]) ? $context["rendered_totals"] : null), "html", null, true));
        echo "
  ";
    }

    public function getTemplateName()
    {
        return "modules/commerce/modules/checkout/templates/commerce-checkout-order-summary.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  110 => 42,  107 => 41,  101 => 38,  91 => 35,  85 => 33,  79 => 31,  77 => 30,  73 => 29,  70 => 28,  66 => 27,  62 => 25,  59 => 24,  55 => 44,  52 => 41,  50 => 24,  45 => 23,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Twig_Source("", "modules/commerce/modules/checkout/templates/commerce-checkout-order-summary.html.twig", "/Users/mulate1/Sites/devdesktop/nexus-event-cams-store/modules/commerce/modules/checkout/templates/commerce-checkout-order-summary.html.twig");
    }
}
