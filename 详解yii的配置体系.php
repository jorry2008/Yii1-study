<?php
/**
 * yii是如何配置的：
 * 首先确认的是，yii的前端管制器CWebApplication对整个应用起到一个最基础的管理和设置，
 * 所有其它组件都或多或少的直接继承与应用对应的main.php配置文件。
 * 
 * 首先，那些直接在main.php的一维元素下配置的内容，我们称之为基础配置，这些配置通常会延伸到module,controller,action,model,theme等
 * 它们都有一定的层级关系，最具体的那层关系优先级最高，有时CWebApplication会提供一个默认的值，但有时是没有的，这些都是正确合理的。
 * 例如：layout布局文件，它可以被CWebApplication直接默认提供，也可以在module中单独提供，在controller重写，在控制器子类中重写，
 * 甚至在最终的action体中重写。
 * 同样CWebApplication::theme属性可以默认指定主题名，而主题启动的时间是CController::render开始，所以如果想动态控制主题对CWebApplication::theme重写
 * 那么一定要在控制这里动态修改即可，最好的方法是在自定义总控制器中设置。
 * 
 * 其次，yii框架是基于组件开发的，那么，我们同样可以在main.php配置文件中对任何组件的id进行单独配置，典型的例子就是数据库配置，
 * 和用户认证系统。但不是所有的组件都是可配置的，即使组件本身已经提供了public属性，
 * 比如：主题管理对象CThemeManager就是不可配置的，尽管它是一个工厂类，管理了CTheme主题对象，CTheme主题对象管理着所有的主题包
 * 尽量它是组件开发，也尽量它提供了属性可控，但有时候对于web系统来说，功能已经足够了，拓展已经到了极限需求。这时
 * 对应组件的id就没有给对应组件的set方法，因而，组件只读。
 * 
 * 综合所述：
 * 1.基本延伸性配置都在CWebApplication中的所有public属性中定义了，在配置文件中处于一维元素。
 * 2.可配置组件是以组件id配置在main文件中的二维数据中的，并且在CWebApplication中提供了set方法，即可写。
 * 3.不可配置组件则只提供了get方法。
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */