<?php
/**
 * @copyright Copyright 2021 Undefined.team
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace app\controllers;

use app\models\Line;
use app\models\User;
use app\models\UserLines;
use Exception;
use Yii;
use yii\data\ActiveDataProvider;
use yii\data\Pagination;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\Response;

class AccessController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'update'],
                'rules' => [
                    [
                        'actions' => ['index', 'update'],
                        'allow' => true,
                        'roles' => ['@'],
                        'matchCallback' => function () {
                            return User::isUserAdmin(Yii::$app->getUser()->getIdentity());
                        }
                    ]
                ],
            ],
        ];
    }

    public function actionIndex(): string
    {
        $pages = new Pagination([
            'pageSize' => 10,
            'forcePageParam' => false,
            'pageSizeParam' => false
        ]);
        $dataProvider = new ActiveDataProvider([
            'query' => User::find(),
            'pagination' => $pages,
        ]);
        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param $id
     * @return string|Response
     * @throws Exception
     */
    public function actionUpdate($id)
    {
        $user = User::findIdentity($id);
        $post = Yii::$app->request->post();
        if ($post) {
            $newLines = ArrayHelper::getValue($post, 'User.linesArray', []);
            UserLines::deleteAll(['user_id' => $id]);
            foreach ($newLines as $newLine) {
                $ul = new UserLines();
                $ul->user_id = $id;
                $ul->line_id = $newLine;
                $ul->save();
            }
            Yii::$app->session->setFlash('success', Yii::t('goip', 'Rules was updated'));
            return $this->redirect(["access/index"]);
        }
        $lines = ArrayHelper::map(Line::find()->all(), 'id', function ($item) {
            return sprintf('Порт: %s %s', $item['number'], $item['title']);
        });
        return $this->render('update', [
            'user' => $user,
            'lines' => $lines,
        ]);
    }
}
